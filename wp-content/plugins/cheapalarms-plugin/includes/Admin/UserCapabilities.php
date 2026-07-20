<?php

namespace CheapAlarms\Plugin\Admin;

use CheapAlarms\Plugin\Services\AuthorizationService;
use CheapAlarms\Plugin\Services\Container;

use function add_action;
use function current_user_can;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function get_userdata;
use function in_array;
use function wp_get_current_user;

class UserCapabilities
{
    private AuthorizationService $authorization;

    public function __construct(Container $container)
    {
        $this->authorization = $container->get(AuthorizationService::class);
    }

    public function register(): void
    {
        add_action('show_user_profile', [$this, 'renderUserRolePanel']);
        add_action('edit_user_profile', [$this, 'renderUserRolePanel']);
    }

    /**
     * Show product role on WP user screen (assign WP role via Users → Role dropdown).
     */
    public function renderUserRolePanel(\WP_User $user): void
    {
        if (!current_user_can('promote_users')) {
            return;
        }

        $resolved = $this->authorization->resolveForUser($user);
        $wpRoles = $user->roles ?? [];
        ?>
        <h2><?php esc_html_e('CheapAlarms access', 'cheapalarms'); ?></h2>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Product role', 'cheapalarms'); ?></th>
                <td>
                    <p>
                        <strong><?php echo esc_html($resolved['role_label']); ?></strong>
                        <span class="description">(<?php echo esc_html($resolved['role_key']); ?>)</span>
                    </p>
                    <p class="description">
                        <?php esc_html_e('Set the WordPress role using the Role dropdown above:', 'cheapalarms'); ?>
                        <strong><?php esc_html_e('Portal Customer', 'cheapalarms'); ?></strong>,
                        <strong><?php esc_html_e('Portal Admin', 'cheapalarms'); ?></strong>, or
                        <strong><?php esc_html_e('Portal Superadmin', 'cheapalarms'); ?></strong>.
                    </p>
                    <p class="description">
                        <?php esc_html_e('Legacy roles ca_moderator / ca_support are treated as Staff for API access. Prefer Portal Admin for new staff.', 'cheapalarms'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Permissions', 'cheapalarms'); ?></th>
                <td>
                    <ul class="ul-disc" style="margin-left:1.25em; list-style:disc;">
                        <?php foreach ($resolved['permissions'] as $permission) : ?>
                            <li><code><?php echo esc_html($permission); ?></code></li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="description">
                        <?php esc_html_e('admin.access = headless admin app · portal.access = customer portal', 'cheapalarms'); ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('WordPress roles', 'cheapalarms'); ?></th>
                <td>
                    <code><?php echo esc_html(implode(', ', $wpRoles) ?: ', '); ?></code>
                </td>
            </tr>
        </table>
        <?php
    }
}
