<?php

namespace CheapAlarms\Plugin\Admin;

use CheapAlarms\Plugin\Services\ChatConversationService;
use CheapAlarms\Plugin\Services\Container;

use function add_action;
use function add_menu_page;
use function admin_url;
use function current_user_can;
use function esc_attr;
use function esc_html;
use function esc_html_e;
use function esc_url;
use function sanitize_text_field;
use function wp_die;

class ChatConversationsAdmin
{
    public function __construct(private Container $container)
    {
    }

    public function register(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
    }

    public function registerMenu(): void
    {
        add_menu_page(
            __('Chat Leads', 'cheapalarms'),
            __('Chat Leads', 'cheapalarms'),
            'ca_manage_portal',
            'ca-chat-leads',
            [$this, 'renderPage'],
            'dashicons-format-chat',
            58
        );
    }

    public function renderPage(): void
    {
        if (!current_user_can('ca_manage_portal')) {
            wp_die(esc_html__('You do not have permission to view chat leads.', 'cheapalarms'));
        }

        $service = $this->container->get(ChatConversationService::class);
        $viewId  = isset($_GET['conversation']) ? (int) $_GET['conversation'] : 0;

        if ($viewId > 0) {
            $this->renderDetail($service, $viewId);

            return;
        }

        $status = isset($_GET['status']) ? sanitize_text_field((string) $_GET['status']) : '';
        $page   = max(1, (int) ($_GET['paged'] ?? 1));
        $limit  = 30;
        $offset = ($page - 1) * $limit;
        $result = $service->listRecent($limit, $offset, $status !== '' ? $status : null);

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Website chat leads', 'cheapalarms') . '</h1>';
        echo '<p class="description">' . esc_html__('Conversations from the Safeguard assistant widget.', 'cheapalarms') . '</p>';

        echo '<ul class="subsubsub">';
        $filters = [
            ''               => __('All', 'cheapalarms'),
            'lead_captured'  => __('Callbacks', 'cheapalarms'),
            'quote_priced'   => __('Quoted', 'cheapalarms'),
            'quote_submitted'=> __('Quote sent', 'cheapalarms'),
        ];
        foreach ($filters as $key => $label) {
            $url   = admin_url('admin.php?page=ca-chat-leads' . ($key !== '' ? '&status=' . rawurlencode($key) : ''));
            $class = ($status === $key) ? 'current' : '';
            echo '<li><a class="' . esc_attr($class) . '" href="' . esc_url($url) . '">' . esc_html($label) . '</a> | </li>';
        }
        echo '</ul>';

        echo '<table class="widefat striped" style="margin-top:1rem">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('Updated', 'cheapalarms') . '</th>';
        echo '<th>' . esc_html__('Status', 'cheapalarms') . '</th>';
        echo '<th>' . esc_html__('Service / page', 'cheapalarms') . '</th>';
        echo '<th>' . esc_html__('Intent', 'cheapalarms') . '</th>';
        echo '<th>' . esc_html__('Quote', 'cheapalarms') . '</th>';
        echo '<th>' . esc_html__('GHL', 'cheapalarms') . '</th>';
        echo '</tr></thead><tbody>';

        if ($result['items'] === []) {
            echo '<tr><td colspan="6">' . esc_html__('No conversations yet.', 'cheapalarms') . '</td></tr>';
        }

        foreach ($result['items'] as $row) {
            $id     = (int) ($row['id'] ?? 0);
            $detail = admin_url('admin.php?page=ca-chat-leads&conversation=' . $id);
            echo '<tr>';
            echo '<td><a href="' . esc_url($detail) . '">' . esc_html((string) ($row['updated_at'] ?? '')) . '</a></td>';
            echo '<td>' . esc_html((string) ($row['status'] ?? '')) . '</td>';
            echo '<td>' . esc_html((string) ($row['page_service'] ?? 'general')) . '<br><code>' . esc_html((string) ($row['page_path'] ?? '')) . '</code></td>';
            echo '<td>' . esc_html((string) ($row['intent'] ?? ', ')) . '</td>';
            $total = $row['quote_total'] ?? null;
            echo '<td>' . ($total !== null && $total !== '' ? esc_html('$' . number_format((float) $total, 0)) : ', ') . '</td>';
            $contactId = (string) ($row['ghl_contact_id'] ?? '');
            echo '<td>' . ($contactId !== '' ? '<code>' . esc_html($contactId) . '</code>' : ', ') . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
    }

    private function renderDetail(ChatConversationService $service, int $id): void
    {
        $conversation = $service->getWithMessages($id);
        if ($conversation === null) {
            wp_die(esc_html__('Conversation not found.', 'cheapalarms'));
        }

        $back = admin_url('admin.php?page=ca-chat-leads');

        echo '<div class="wrap">';
        echo '<p><a href="' . esc_url($back) . '">&larr; ' . esc_html__('Back to list', 'cheapalarms') . '</a></p>';
        echo '<h1>' . esc_html__('Chat conversation', 'cheapalarms') . ' #' . esc_html((string) $id) . '</h1>';

        echo '<table class="form-table"><tbody>';
        echo '<tr><th>' . esc_html__('Status', 'cheapalarms') . '</th><td>' . esc_html((string) ($conversation['status'] ?? '')) . '</td></tr>';
        echo '<tr><th>' . esc_html__('Page', 'cheapalarms') . '</th><td><code>' . esc_html((string) ($conversation['page_path'] ?? '')) . '</code></td></tr>';
        echo '<tr><th>' . esc_html__('GHL contact', 'cheapalarms') . '</th><td><code>' . esc_html((string) ($conversation['ghl_contact_id'] ?? ', ')) . '</code></td></tr>';
        echo '<tr><th>' . esc_html__('Estimate', 'cheapalarms') . '</th><td><code>' . esc_html((string) ($conversation['estimate_id'] ?? ', ')) . '</code></td></tr>';
        echo '</tbody></table>';

        echo '<h2>' . esc_html__('Transcript', 'cheapalarms') . '</h2>';
        echo '<div style="max-width:720px;background:#fff;border:1px solid #ccd0d4;padding:1rem">';

        foreach ($conversation['messages'] ?? [] as $msg) {
            $role = esc_html((string) ($msg['role'] ?? ''));
            $time = esc_html((string) ($msg['created_at'] ?? ''));
            $text = esc_html((string) ($msg['content'] ?? ''));
            echo '<div style="margin-bottom:0.75rem;padding-bottom:0.75rem;border-bottom:1px solid #eee">';
            echo '<strong>' . $role . '</strong> <span style="color:#666;font-size:12px">' . $time . '</span>';
            echo '<p style="margin:0.35rem 0 0;white-space:pre-wrap">' . $text . '</p>';
            echo '</div>';
        }

        echo '</div></div>';
    }
}
