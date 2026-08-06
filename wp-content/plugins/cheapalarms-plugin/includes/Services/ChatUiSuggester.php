<?php



namespace CheapAlarms\Plugin\Services;



use function home_url;

use function strtolower;



/**

 * Suggests inline chat UI (forms, links, pickers) based on conversation, no preset footer buttons.

 */

class ChatUiSuggester

{

    public function __construct(private ChatRouterService $router)

    {

    }



    /**

     * @param array<int, array{role: string, content: string}> $messages

     * @param array<string, mixed>                             $context pageContext, clientState

     * @return array<string, mixed>|null

     */

    public function suggest(array $messages, string $reply, array $context = []): ?array

    {

        $clientState = is_array($context['clientState'] ?? null) ? $context['clientState'] : [];

        $leadDone    = !empty($clientState['leadSubmitted']);
        $quoteDone   = !empty($clientState['quoteSubmitted']);
        $quoteReady  = !empty($context['quoteSession']['resolveToken']);
        $handoffOn   = !empty($clientState['handoffActive']);

        if ($quoteDone || $quoteReady || $handoffOn) {
            return null;
        }

        if ($leadDone) {
            return null;
        }



        $lastUser = $this->lastUserMessage($messages);

        $haystack = strtolower($lastUser . ' ' . $reply);



        if ($this->matches($haystack, '/\b(urgent|emergency|right now|immediately)\b/u')) {

            return [

                'type'  => 'links',

                'items' => [

                    [

                        'label' => 'Call 1300 225 276',

                        'href'  => 'tel:1300225276',

                    ],

                ],

            ];

        }



        if ($this->matches($haystack, '/\b(which system|what system|not sure|don\'?t know|help me choose|what do i need)\b/u')) {

            return ['type' => 'service_picker'];

        }



        if (

            $this->matches($haystack, '/\b(ajax|instant quote|alarm quote|price my|how much.*alarm|quote my|kit price|wireless alarm|how much|what.*cost|pricing)\b/u')

            && $this->matches($haystack, '/\b(alarm|ajax|wireless|intrusion)\b/u')

        ) {

            return ['type' => 'quote_chat'];

        }



        if (

            $this->matches($haystack, '/\b(speak to (a )?(real |human |live )?person|talk to (a )?(real |human |live )?(person|agent|someone|human)|real (person|human|agent)|live (agent|chat|person)|human agent)\b/u')

        ) {

            return [

                'type'   => 'agent_handoff',

                'intent' => 'agent_handoff',

            ];

        }



        if (

            $this->matches($haystack, '/\b(call me back|callback|call back|ring me|contact me|my details|phone number|get a quote|request a quote|leave my details)\b/u')

        ) {

            return [

                'type'   => 'lead_form',

                'intent' => $this->detectIntent($haystack, $context),

            ];

        }



        if (

            $this->matches($haystack, '/\b(quote|pricing|estimate|site visit|ballpark)\b/u')

            && $this->matches($haystack, '/\b(cctv|camera|access|intercom|monitoring)\b/u')

            && !$this->matches($haystack, '/\b(ajax|alarm)\b/u')

        ) {

            $route = $this->router->recommend($lastUser);



            return [

                'type'  => 'route',

                'route' => $route,

            ];

        }



        if (

            $this->matches($reply, '/\b(name and (mobile|phone)|leave your details|fill in|details below|form below|share your name)\b/ui')

        ) {

            return [

                'type'   => 'lead_form',

                'intent' => $this->detectIntent($haystack, $context),

            ];

        }



        if (

            $this->matches($haystack, '/\b(cctv|camera|surveillance)\b/u')

            && $this->matches($haystack, '/\b(quote|price|cost|install)\b/u')

        ) {

            return [

                'type'  => 'links',

                'items' => [

                    ['label' => 'CCTV systems', 'href' => home_url('/cctv/')],

                    ['label' => 'Request a callback', 'action' => 'lead_form', 'intent' => 'cctv'],

                ],

            ];

        }



        return null;

    }



    /**

     * @param array<int, array{role: string, content: string}> $messages

     */

    private function lastUserMessage(array $messages): string

    {

        for ($i = count($messages) - 1; $i >= 0; $i--) {

            if (($messages[$i]['role'] ?? '') === 'user') {

                return (string) ($messages[$i]['content'] ?? '');

            }

        }



        return '';

    }



    private function matches(string $haystack, string $pattern): bool

    {

        return (bool) preg_match($pattern, $haystack);

    }



    /**

     * @param array<string, mixed> $context

     */

    private function detectIntent(string $haystack, array $context): string

    {

        $service = (string) ($context['pageContext']['service'] ?? '');

        if ($service !== '' && $service !== 'general') {

            return $service;

        }



        if ($this->matches($haystack, '/\b(cctv|camera)\b/u')) {

            return 'cctv';

        }

        if ($this->matches($haystack, '/\b(access)\b/u')) {

            return 'access_control';

        }

        if ($this->matches($haystack, '/\b(intercom|doorbell)\b/u')) {

            return 'intercom';

        }

        if ($this->matches($haystack, '/\b(alarm|ajax)\b/u')) {

            return 'alarms';

        }



        return 'quote';

    }

}

