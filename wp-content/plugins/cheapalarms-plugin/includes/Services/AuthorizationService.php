<?php



namespace CheapAlarms\Plugin\Services;



use WP_Error;

use WP_User;



use function __;

use function array_merge;

use function array_values;

use function get_option;

use function get_user_by;

use function get_user_meta;

use function get_users;

use function in_array;

use function is_array;

use function json_decode;

use function sanitize_text_field;

use function update_option;

use function update_user_meta;

use function wp_json_encode;



/**

 * Single source of truth for CheapAlarms product roles and permissions.

 *

 * Product roles: customer | staff | owner

 * Legacy WP role slugs (ca_customer, ca_admin, …) map into these.

 */

class AuthorizationService

{

    public const ROLE_CUSTOMER = 'customer';

    public const ROLE_STAFF = 'staff';

    public const ROLE_OWNER = 'owner';



    public const WP_ROLE_CUSTOMER = 'ca_customer';

    public const WP_ROLE_STAFF = 'ca_admin';

    public const WP_ROLE_OWNER = 'ca_superadmin';



    public const META_ALLOWED_LOCATIONS = 'ca_allowed_location_ids';



    private const AUDIT_OPTION = 'ca_role_change_audit';



    /** @var array<string, string> */

    public const LEGACY_CAP_TO_PERMISSION = [

        'ca_manage_portal'    => 'admin.access',

        'ca_access_portal'    => 'portal.access',

        'ca_view_estimates'   => 'estimates.view',

        'ca_manage_support'   => 'support.manage',

        'ca_invite_customers' => 'customers.invite',

        'ca_manage_settings'  => 'settings.manage',

    ];



    /** @var array<string, string> Product role → primary WP role slug */

    public const PRODUCT_ROLE_TO_WP = [

        self::ROLE_CUSTOMER => self::WP_ROLE_CUSTOMER,

        self::ROLE_STAFF    => self::WP_ROLE_STAFF,

        self::ROLE_OWNER    => self::WP_ROLE_OWNER,

    ];



    /** @var array<string, array<string, bool>> */

    private const PERMISSIONS_BY_ROLE = [

        self::ROLE_CUSTOMER => [

            'portal.access' => true,

        ],

        self::ROLE_STAFF => [

            'portal.access'     => true,

            'admin.access'      => true,

            'estimates.view'    => true,

            'estimates.manage'  => true,

            'invoices.view'     => true,

            'invoices.manage'   => true,

            'customers.view'    => true,

            'customers.invite'  => true,

            'integrations.view' => true,

            'support.manage'    => true,

        ],

        self::ROLE_OWNER => [

            'portal.access'       => true,

            'admin.access'        => true,

            'estimates.view'      => true,

            'estimates.manage'    => true,

            'invoices.view'       => true,

            'invoices.manage'     => true,

            'customers.view'      => true,

            'customers.invite'    => true,

            'integrations.view'   => true,

            'integrations.manage' => true,

            'support.manage'      => true,

            'settings.manage'     => true,

            'data.destructive'    => true,

        ],

    ];



    /** WP role slug → product role (first match wins by priority). */

    private const WP_ROLE_TO_PRODUCT = [

        'ca_superadmin' => self::ROLE_OWNER,

        'ca_admin'      => self::ROLE_STAFF,

        'ca_moderator'  => self::ROLE_STAFF,

        'ca_support'    => self::ROLE_STAFF,

        'ca_customer'   => self::ROLE_CUSTOMER,

        'customer'      => self::ROLE_CUSTOMER,

    ];



    /**

     * @return array{role_key: string, role_label: string, permissions: string[], is_admin: bool, is_customer: bool, legacy_roles: string[], allowed_location_ids: string[]}

     */

    public function resolveForUser(WP_User $user): array

    {

        $wpRoles = array_values($user->roles ?? []);

        $roleKey = $this->resolveProductRole($user, $wpRoles);

        $permissions = $this->permissionsForRole($roleKey);



        if (in_array('administrator', $wpRoles, true)) {

            $roleKey = self::ROLE_OWNER;

            $permissions = $this->permissionsForRole(self::ROLE_OWNER);

        }



        $isAdmin = $this->hasPermission($permissions, 'admin.access');

        $isCustomer = $roleKey === self::ROLE_CUSTOMER && !$isAdmin;



        return [

            'role_key'               => $roleKey,

            'role_label'             => $this->labelForRole($roleKey),

            'permissions'            => $permissions,

            'is_admin'               => $isAdmin,

            'is_customer'            => $isCustomer,

            'legacy_roles'           => $wpRoles,

            'allowed_location_ids'   => $this->getAllowedLocationIds($user),

        ];

    }



    public function can(WP_User $user, string $permission): bool

    {

        if ($user->has_cap('manage_options')) {

            return true;

        }



        $resolved = $this->resolveForUser($user);

        if ($this->hasPermission($resolved['permissions'], $permission)) {

            return true;

        }



        foreach (self::LEGACY_CAP_TO_PERMISSION as $legacyCap => $mappedPermission) {

            if ($mappedPermission === $permission && $user->has_cap($legacyCap)) {

                return true;

            }

        }



        return false;

    }



    public function canLegacyCap(WP_User $user, string $legacyCap): bool

    {

        if ($user->has_cap('manage_options')) {

            return true;

        }



        $permission = self::LEGACY_CAP_TO_PERMISSION[$legacyCap] ?? null;

        if ($permission !== null && $this->can($user, $permission)) {

            return true;

        }



        return $user->has_cap($legacyCap);

    }



    /**

     * Roles the actor may assign via the portal API.

     *

     * @return array<int, array{role_key: string, role_label: string, wp_role: string, description: string}>

     */

    public function getAssignableRoles(WP_User $actor): array

    {

        $actorRole = $this->resolveForUser($actor)['role_key'];

        $catalog = $this->getRoleCatalog();



        if ($actorRole === self::ROLE_OWNER || $actor->has_cap('manage_options')) {

            return $catalog;

        }



        if ($actorRole === self::ROLE_STAFF) {

            return array_values(array_filter(

                $catalog,

                static fn (array $row): bool => $row['role_key'] === self::ROLE_CUSTOMER

            ));

        }



        return [];

    }



    /**

     * @return array<int, array{role_key: string, role_label: string, wp_role: string, description: string}>

     */

    public function getRoleCatalog(): array

    {

        return [

            [

                'role_key'    => self::ROLE_CUSTOMER,

                'role_label'  => $this->labelForRole(self::ROLE_CUSTOMER),

                'wp_role'     => self::WP_ROLE_CUSTOMER,

                'description' => __('Customer portal only, no admin app.', 'cheapalarms'),

            ],

            [

                'role_key'    => self::ROLE_STAFF,

                'role_label'  => $this->labelForRole(self::ROLE_STAFF),

                'wp_role'     => self::WP_ROLE_STAFF,

                'description' => __('Day-to-day admin app access (estimates, invoices, customers).', 'cheapalarms'),

            ],

            [

                'role_key'    => self::ROLE_OWNER,

                'role_label'  => $this->labelForRole(self::ROLE_OWNER),

                'wp_role'     => self::WP_ROLE_OWNER,

                'description' => __('Full access including settings and destructive operations.', 'cheapalarms'),

            ],

        ];

    }



    /**
     * @return true|WP_Error
     */
    public function validateNewUserRole(WP_User $actor, string $roleKey): bool|WP_Error
    {
        $roleKey = sanitize_text_field($roleKey);
        if (!isset(self::PRODUCT_ROLE_TO_WP[$roleKey])) {
            return new WP_Error('bad_request', __('Invalid role.', 'cheapalarms'), ['status' => 400]);
        }

        $allowedKeys = array_column($this->getAssignableRoles($actor), 'role_key');
        if (!in_array($roleKey, $allowedKeys, true)) {
            return new WP_Error('forbidden', __('You cannot assign this role.', 'cheapalarms'), ['status' => 403]);
        }

        $actorRole = $this->resolveForUser($actor)['role_key'];
        if ($this->roleRank($roleKey) > $this->roleRank($actorRole)) {
            return new WP_Error('forbidden', __('You cannot create a user above your own access level.', 'cheapalarms'), ['status' => 403]);
        }

        if ($actorRole === self::ROLE_STAFF && $roleKey !== self::ROLE_CUSTOMER) {
            return new WP_Error('forbidden', __('Staff can only create customer accounts.', 'cheapalarms'), ['status' => 403]);
        }

        return true;
    }

    /**
     * @return true|WP_Error
     */
    public function validateActorCanChangeTargetRole(WP_User $actor, WP_User $target, string $newRoleKey): bool|WP_Error
    {
        $actorRole = $this->resolveForUser($actor)['role_key'];
        $targetRole = $this->resolveForUser($target)['role_key'];
        $actorRank = $this->roleRank($actorRole);
        $targetRank = $this->roleRank($targetRole);
        $newRank = $this->roleRank($newRoleKey);

        if ($targetRank > $actorRank) {
            return new WP_Error(
                'forbidden',
                __('You cannot change the role of a user with higher access than you.', 'cheapalarms'),
                ['status' => 403]
            );
        }

        if ($newRank > $actorRank) {
            return new WP_Error(
                'forbidden',
                __('You cannot assign a role above your own access level.', 'cheapalarms'),
                ['status' => 403]
            );
        }

        if ($actorRole === self::ROLE_STAFF) {
            // Staff may assign customer role only to customer-level users (e.g. new subscriber → ca_customer).
            // They cannot demote staff/owners or change anyone with admin app access.
            if ($newRoleKey === self::ROLE_CUSTOMER
                && $targetRole === self::ROLE_CUSTOMER
                && $targetRank < $actorRank
            ) {
                return true;
            }

            return new WP_Error(
                'forbidden',
                __('Staff cannot change user roles. Contact an owner.', 'cheapalarms'),
                ['status' => 403]
            );
        }

        return true;
    }

    /**
     * @param string[]|null $allowedLocationIds Empty/null = unrestricted (all locations).
     * @return true|WP_Error
     */
    public function assignProductRole(WP_User $target, string $roleKey, WP_User $actor, ?array $allowedLocationIds = null): bool|WP_Error
    {
        $roleKey = sanitize_text_field($roleKey);
        if (!isset(self::PRODUCT_ROLE_TO_WP[$roleKey])) {
            return new WP_Error('bad_request', __('Invalid role.', 'cheapalarms'), ['status' => 400]);
        }

        $assignable = $this->getAssignableRoles($actor);
        $allowedKeys = array_column($assignable, 'role_key');
        if (!in_array($roleKey, $allowedKeys, true)) {
            return new WP_Error('forbidden', __('You cannot assign this role.', 'cheapalarms'), ['status' => 403]);
        }

        $hierarchyCheck = $this->validateActorCanChangeTargetRole($actor, $target, $roleKey);
        if (is_wp_error($hierarchyCheck)) {
            return $hierarchyCheck;
        }

        $targetRole = $this->resolveForUser($target)['role_key'];
        if ($targetRole === self::ROLE_OWNER && $roleKey !== self::ROLE_OWNER) {
            $remainingOwners = $this->countUsersWithProductRole(self::ROLE_OWNER);
            if ($remainingOwners <= 1) {
                return new WP_Error('forbidden', __('Cannot demote the last owner account.', 'cheapalarms'), ['status' => 403]);
            }
        }

        if ($target->ID === $actor->ID && $roleKey !== self::ROLE_OWNER && $this->resolveForUser($actor)['role_key'] === self::ROLE_OWNER) {
            $remainingOwners = $this->countUsersWithProductRole(self::ROLE_OWNER);
            if ($remainingOwners <= 1) {
                return new WP_Error('forbidden', __('Cannot remove the last owner account from yourself.', 'cheapalarms'), ['status' => 403]);
            }
        }

        if ($target->has_cap('manage_options') && $roleKey !== self::ROLE_OWNER) {
            return new WP_Error('forbidden', __('WordPress administrators must remain owners.', 'cheapalarms'), ['status' => 403]);
        }

        if ($allowedLocationIds !== null && !$this->can($actor, 'settings.manage')) {
            return new WP_Error('forbidden', __('Only owners can set location scope.', 'cheapalarms'), ['status' => 403]);
        }

        $wpRole = self::PRODUCT_ROLE_TO_WP[$roleKey];
        $target->set_role($wpRole);

        if ($allowedLocationIds !== null) {
            $this->setAllowedLocationIds($target, $allowedLocationIds);
        }

        $this->appendRoleAudit($actor, $target, $roleKey);

        return true;
    }



    /**

     * @return string[] Empty = no restriction (all locations).

     */

    public function getAllowedLocationIds(WP_User $user): array

    {

        $raw = get_user_meta($user->ID, self::META_ALLOWED_LOCATIONS, true);

        if ($raw === '' || $raw === false || $raw === null) {

            return [];

        }



        if (is_string($raw)) {

            $decoded = json_decode($raw, true);

            if (!is_array($decoded)) {

                return [];

            }

            $raw = $decoded;

        }



        if (!is_array($raw)) {

            return [];

        }



        $out = [];

        foreach ($raw as $id) {

            $id = sanitize_text_field((string) $id);

            if ($id !== '') {

                $out[] = $id;

            }

        }



        return array_values(array_unique($out));

    }



    /**

     * @param string[] $locationIds

     */

    public function setAllowedLocationIds(WP_User $user, array $locationIds): void

    {

        $clean = [];

        foreach ($locationIds as $id) {

            $id = sanitize_text_field((string) $id);

            if ($id !== '') {

                $clean[] = $id;

            }

        }

        $clean = array_values(array_unique($clean));

        update_user_meta($user->ID, self::META_ALLOWED_LOCATIONS, wp_json_encode($clean) ?: '[]');

    }



    public function userCanAccessLocation(WP_User $user, string $locationId): bool

    {

        if ($user->has_cap('manage_options')) {

            return true;

        }



        $allowed = $this->getAllowedLocationIds($user);

        if ($allowed === []) {

            return true;

        }



        return in_array($locationId, $allowed, true);

    }



    /**

     * @return array<int, array<string, mixed>>

     */

    public function listStaffUsers(int $limit = 100): array

    {

        $limit = min(max(1, $limit), 200);

        $users = get_users([

            'role__in' => ['ca_admin', 'ca_superadmin', 'ca_moderator', 'ca_support', 'administrator'],

            'number'   => $limit,

            'orderby'  => 'registered',

            'order'    => 'DESC',

        ]);



        $formatted = [];

        foreach ($users as $user) {

            if (!$user instanceof WP_User) {

                continue;

            }

            $resolved = $this->resolveForUser($user);

            if (!$resolved['is_admin']) {

                continue;

            }

            $formatted[] = [

                'id'                   => $user->ID,

                'email'                => $user->user_email,

                'name'                 => $user->display_name,

                'role_key'             => $resolved['role_key'],

                'role_label'           => $resolved['role_label'],

                'wp_roles'             => $resolved['legacy_roles'],

                'permissions'          => $resolved['permissions'],

                'allowed_location_ids' => $resolved['allowed_location_ids'],

                'registered'           => $user->user_registered,

            ];

        }



        return $formatted;

    }



    public function normalizeLegacyCustomerUsers(): int

    {

        $users = get_users([

            'role'   => 'customer',

            'number' => 500,

        ]);



        $count = 0;

        foreach ($users as $user) {

            if (!$user instanceof WP_User) {

                continue;

            }

            $user->set_role(self::WP_ROLE_CUSTOMER);

            ++$count;

        }



        return $count;

    }



    /**

     * @param string[] $wpRoles

     */

    private function resolveProductRole(WP_User $user, array $wpRoles): string

    {

        $priority = ['ca_superadmin', 'ca_admin', 'ca_moderator', 'ca_support', 'ca_customer', 'customer'];

        foreach ($priority as $slug) {

            if (in_array($slug, $wpRoles, true)) {

                return self::WP_ROLE_TO_PRODUCT[$slug] ?? self::ROLE_CUSTOMER;

            }

        }



        if ($user->has_cap('ca_manage_portal') || $user->has_cap('ca_view_estimates')) {

            return self::ROLE_STAFF;

        }



        if ($user->has_cap('ca_access_portal')) {

            return self::ROLE_CUSTOMER;

        }



        return self::ROLE_CUSTOMER;

    }



    private function countUsersWithProductRole(string $roleKey): int

    {

        $wpRole = self::PRODUCT_ROLE_TO_WP[$roleKey] ?? null;

        if ($wpRole === null) {

            return 0;

        }



        $users = get_users(['role' => $wpRole, 'fields' => 'ID']);

        $count = is_array($users) ? count($users) : 0;



        if ($roleKey === self::ROLE_OWNER) {

            $admins = get_users(['role' => 'administrator', 'fields' => 'ID']);

            $count += is_array($admins) ? count($admins) : 0;

        }



        return $count;

    }



    private function appendRoleAudit(WP_User $actor, WP_User $target, string $roleKey): void

    {

        $log = get_option(self::AUDIT_OPTION, []);

        if (!is_array($log)) {

            $log = [];

        }



        array_unshift($log, [

            'at'              => gmdate('c'),

            'actor_id'        => $actor->ID,

            'actor_email'     => $actor->user_email,

            'target_id'       => $target->ID,

            'target_email'    => $target->user_email,

            'role_key'        => $roleKey,

        ]);



        $log = array_slice($log, 0, 100);

        update_option(self::AUDIT_OPTION, $log, false);

    }



    /**

     * @return string[]

     */

    private function permissionsForRole(string $roleKey): array

    {

        $map = self::PERMISSIONS_BY_ROLE[$roleKey] ?? self::PERMISSIONS_BY_ROLE[self::ROLE_CUSTOMER];

        $out = [];

        foreach ($map as $permission => $granted) {

            if ($granted) {

                $out[] = $permission;

            }

        }



        return $out;

    }



    /**

     * @param string[] $permissions

     */

    private function hasPermission(array $permissions, string $permission): bool

    {

        return in_array($permission, $permissions, true);

    }



    private function roleRank(string $roleKey): int
    {
        return match ($roleKey) {
            self::ROLE_OWNER => 2,
            self::ROLE_STAFF => 1,
            default          => 0,
        };
    }

    private function labelForRole(string $roleKey): string

    {

        return match ($roleKey) {

            self::ROLE_OWNER   => __('Owner', 'cheapalarms'),

            self::ROLE_STAFF   => __('Staff', 'cheapalarms'),

            default            => __('Customer', 'cheapalarms'),

        };

    }

}


