(function () {

  'use strict';



  function init() {

    var root = document.getElementById('sg-chat');

    if (!root) {

      return;

    }



    var cfg = window.sgChatConfig || {};

    var apiUrl = cfg.apiUrl || root.getAttribute('data-api-url') || '';

    var leadUrl = cfg.leadUrl || '';

    var routeUrl = cfg.routeUrl || '';

    var pollUrl = cfg.pollUrl || '';

    if (!apiUrl) {

      return;

    }



    var panel = document.getElementById('sg-chat-panel');

    var toggle = document.getElementById('sg-chat-toggle');

    var closeBtn = document.getElementById('sg-chat-close');

    var newChatBtn = document.getElementById('sg-chat-new');

    var form = document.getElementById('sg-chat-form');

    var input = document.getElementById('sg-chat-input');

    var sendBtn = document.getElementById('sg-chat-send');

    var messagesEl = document.getElementById('sg-chat-messages');



    if (!panel || !toggle || !form || !input || !messagesEl || !sendBtn) {

      return;

    }



    var history = [];
    var displayThread = [];

    var pageContext = cfg.pageContext || {};

    var isOpen = false;

    var isSending = false;

    var welcomed = false;

    var leadSubmitted = false;

    var handoffActive = false;

    var handoffStatus = '';

    var lastPollMessageId = 0;

    var pollTimer = null;

    var seenPollMessageIds = {};

    var activeLeadForm = null;

    var starterDismissed = false;

    var activeStarterEl = null;

    var conversationKey = null;

    var quoteSubmitUrl = cfg.quoteSubmitUrl || '';

    var otpSendUrl = cfg.otpSendUrl || '';

    var otpVerifyUrl = cfg.otpVerifyUrl || '';

    var quoteSession = null;

    var quoteMode = false;

    var quoteSubmitted = false;

    var activeQuoteForm = null;

    var otpVerifiedToken = null;

    var STORAGE_KEY = 'sgChatState';
    var STORAGE_TTL_MS = 24 * 60 * 60 * 1000;
    var STORAGE_HANDOFF_TTL_MS = 7 * 24 * 60 * 60 * 1000;
    var MAX_STORED_MESSAGES = 20;



    var isRestoring = false;

    function clearPersistedState() {
      try {
        localStorage.removeItem(STORAGE_KEY);
      } catch (e) {
        /* ignore */
      }
      try {
        sessionStorage.removeItem('sgChatConversationKey');
      } catch (e2) {
        /* ignore */
      }
    }

    function persistState() {
      try {
        localStorage.setItem(
          STORAGE_KEY,
          JSON.stringify({
            savedAt: Date.now(),
            history: history.slice(-MAX_STORED_MESSAGES),
            displayThread: displayThread.slice(-MAX_STORED_MESSAGES),
            starterDismissed: starterDismissed,
            leadSubmitted: leadSubmitted,
            handoffActive: handoffActive,
            handoffStatus: handoffStatus,
            lastPollMessageId: lastPollMessageId,
            quoteSubmitted: quoteSubmitted,
            quoteMode: quoteMode,
            quoteSession: quoteSession,
            conversationKey: conversationKey || null,
          })
        );
      } catch (e) {
        /* ignore quota / private mode */
      }
    }



    function loadPersistedState() {
      try {
        var raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) {
          return;
        }

        var state = JSON.parse(raw);
        if (!state || typeof state.savedAt !== 'number') {
          clearPersistedState();
          return;
        }

        // Resolved / timed-out chats start fresh on next visit.
        // Return-to-AI (`open`) must KEEP history — do not wipe it here.
        if (state.handoffStatus === 'resolved' || state.handoffStatus === 'timed_out') {
          clearPersistedState();
          return;
        }

        var ttl = state.handoffActive ? STORAGE_HANDOFF_TTL_MS : STORAGE_TTL_MS;
        if (Date.now() - state.savedAt > ttl) {
          clearPersistedState();
          return;
        }

        if (Array.isArray(state.history)) {
          history = state.history.slice(-MAX_STORED_MESSAGES);
        }

        if (Array.isArray(state.displayThread)) {
          displayThread = state.displayThread.slice(-MAX_STORED_MESSAGES);
        } else if (history.length) {
          displayThread = history.slice();
        }

        welcomed = displayThread.length > 0 || history.length > 0;
        starterDismissed = !!state.starterDismissed;
        leadSubmitted = !!state.leadSubmitted;
        handoffActive = !!state.handoffActive;
        handoffStatus = state.handoffStatus || '';
        lastPollMessageId = Number(state.lastPollMessageId) || 0;
        quoteSubmitted = !!state.quoteSubmitted;
        quoteMode = !!state.quoteMode;
        quoteSession = state.quoteSession || null;

        // After return-to-AI, unlock handoff CTA again.
        if (!handoffActive && handoffStatus === 'open') {
          leadSubmitted = false;
        }

        if (state.conversationKey) {
          conversationKey = state.conversationKey;
          sessionStorage.setItem('sgChatConversationKey', conversationKey);
        }
      } catch (e) {
        clearPersistedState();
      }
    }



    function restoreThreadFromStorage() {
      var thread = displayThread.length ? displayThread : history;

      if (!thread.length) {
        return;
      }

      isRestoring = true;

      thread.forEach(function (entry) {
        if (!entry || !entry.content) {
          return;
        }
        var role = entry.role === 'user' ? 'user' : entry.role === 'agent' ? 'agent' : 'assistant';
        appendMessage(role, entry.content, role === 'agent' ? 'sg-chat__bubble--agent' : '');
      });

      isRestoring = false;

      if (handoffActive) {
        startHandoffPolling();
      } else if (quoteSession && quoteSession.resolveToken && !quoteSubmitted) {
        showQuoteVerifyForm(quoteSession);
      } else if (
        !starterDismissed &&
        !leadSubmitted &&
        history.length === 0 &&
        thread.length <= 1
      ) {
        showStarterChoices();
      }

      if (!handoffActive) {
        ensureAgentEscapeHatch();
      }

      messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function startFreshConversation() {
      stopHandoffPolling();
      removeLeadForm();
      removeQuoteForm();
      dismissStarters();

      history = [];
      displayThread = [];
      welcomed = false;
      starterDismissed = false;
      leadSubmitted = false;
      handoffActive = false;
      handoffStatus = '';
      lastPollMessageId = 0;
      seenPollMessageIds = {};
      quoteSubmitted = false;
      quoteMode = false;
      quoteSession = null;
      otpVerifiedToken = null;
      conversationKey = null;
      activeStarterEl = null;

      clearPersistedState();
      messagesEl.innerHTML = '';

      appendMessage(
        'assistant',
        chatAvailable
          ? cfg.welcome ||
              'Hi, ask anything about alarms, CCTV, access control, or getting a quote for your property.'
          : cfg.unavailable ||
              'The AI assistant is temporarily unavailable. Tap Talk to a person below and our team will help you live.'
      );
      welcomed = true;
      showStarterChoices();
      ensureAgentEscapeHatch();
      persistState();
      input.focus();
    }



    loadPersistedState();



    function getConversationKey() {

      if (conversationKey) {

        return conversationKey;

      }

      try {

        var stored = sessionStorage.getItem('sgChatConversationKey');

        if (stored) {

          conversationKey = stored;

          return conversationKey;

        }

        var key =

          'sg' +

          (window.crypto && window.crypto.getRandomValues

            ? Array.prototype.map

                .call(window.crypto.getRandomValues(new Uint8Array(12)), function (b) {

                  return ('0' + b.toString(16)).slice(-2);

                })

                .join('')

            : Date.now().toString(36) + Math.random().toString(36).slice(2, 14));

        sessionStorage.setItem('sgChatConversationKey', key);

        conversationKey = key;

        return key;

      } catch (e) {

        conversationKey = 'sg-fallback-' + Date.now();

        return conversationKey;

      }

    }



    function saveConversationKey(data) {

      if (data && data.conversationKey) {

        conversationKey = data.conversationKey;

        try {

          sessionStorage.setItem('sgChatConversationKey', conversationKey);

          persistState();

        } catch (e) {

          /* ignore */

        }

      }

    }



    function withConversation(payload) {

      payload.conversationKey = getConversationKey();

      payload.pageContext = pageContext;

      return payload;

    }



    function appendPostSubmitHelp() {

      appendMessage(

        'assistant',

        cfg.postLeadHelp || 'Check spam if you do not hear from us soon.'

      );

    }



    var chatAvailable =

      cfg.available === true ||

      cfg.available === 1 ||

      cfg.available === '1' ||

      root.getAttribute('data-available') === '1';



    function dismissStarters() {

      starterDismissed = true;

      if (activeStarterEl && activeStarterEl.parentNode) {

        activeStarterEl.parentNode.removeChild(activeStarterEl);

      }

      activeStarterEl = null;

      persistState();

    }



    function setOpen(next) {

      isOpen = !!next;

      root.classList.toggle('is-open', isOpen);

      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

      panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');



      if (isOpen) {

        if (messagesEl.children.length === 0) {
          if (displayThread.length || history.length) {
            restoreThreadFromStorage();
          } else if (!welcomed) {
            appendMessage(
              'assistant',
              chatAvailable
                ? cfg.welcome ||
                    'Hi, ask anything about alarms, CCTV, access control, or getting a quote for your property.'
                : cfg.unavailable ||
                    'The AI assistant is temporarily unavailable. Tap Talk to a person below and our team will help you live.'
            );

            welcomed = true;
            showStarterChoices();
            ensureAgentEscapeHatch();
          }
        } else if (!handoffActive) {
          ensureAgentEscapeHatch();
        }

        ensureHandoffChoicesVisible();
        input.focus();
      }

    }



    function ensureHandoffChoicesVisible() {
      if (chatAvailable || leadSubmitted || handoffActive) {
        return;
      }

      showStarterChoices();
      ensureAgentEscapeHatch();
    }

    function ensureAgentEscapeHatch() {
      if (handoffActive || leadSubmitted || activeLeadForm) {
        return;
      }

      if (messagesEl.querySelector('.sg-chat__agent-escape')) {
        return;
      }

      if (activeStarterEl && activeStarterEl.parentNode) {
        var hasAgent = false;
        var chips = activeStarterEl.querySelectorAll('.sg-chat__chip');
        for (var i = 0; i < chips.length; i++) {
          if ((chips[i].textContent || '').toLowerCase().indexOf('talk to a person') !== -1) {
            hasAgent = true;
            break;
          }
        }
        if (hasAgent) {
          return;
        }
      }

      var wrap = document.createElement('div');
      wrap.className = 'sg-chat__inline-actions sg-chat__agent-escape';

      var agentBtn = document.createElement('button');
      agentBtn.type = 'button';
      agentBtn.className = 'sg-chat__chip sg-chat__chip--primary';
      agentBtn.textContent = 'Talk to a person';
      agentBtn.addEventListener('click', function () {
        dismissStarters();
        var escapeEl = messagesEl.querySelector('.sg-chat__agent-escape');
        if (escapeEl && escapeEl.parentNode) {
          escapeEl.parentNode.removeChild(escapeEl);
        }
        appendMessage('user', 'Talk to a person');
        showHandoffForm(true);
      });
      wrap.appendChild(agentBtn);

      var callLink = document.createElement('a');
      callLink.className = 'sg-chat__chip sg-chat__chip--link';
      callLink.href = cfg.phoneHref || 'tel:1300225276';
      callLink.textContent = 'Call us';
      wrap.appendChild(callLink);

      messagesEl.appendChild(wrap);
      messagesEl.scrollTop = messagesEl.scrollHeight;
    }



    function appendThinking() {

      var bubble = document.createElement('div');

      bubble.className = 'sg-chat__msg sg-chat__msg--bot sg-chat__msg--typing';

      bubble.setAttribute('aria-live', 'polite');

      bubble.setAttribute('aria-busy', 'true');



      var label = document.createElement('span');

      label.className = 'sg-chat__typing-label';

      label.textContent = (cfg.thinking || 'Thinking').replace(/…|\.\.\.$/, '');



      var dots = document.createElement('span');

      dots.className = 'sg-chat__typing-dots';

      dots.setAttribute('aria-hidden', 'true');

      for (var i = 0; i < 3; i++) {

        dots.appendChild(document.createElement('span'));

      }



      bubble.appendChild(label);

      bubble.appendChild(dots);

      messagesEl.appendChild(bubble);

      messagesEl.scrollTop = messagesEl.scrollHeight;

      return bubble;

    }



    function appendMessage(role, text, extraClass) {

      var bubble = document.createElement('div');

      var isUser = role === 'user';
      var isAgent = role === 'agent';

      bubble.className =

        'sg-chat__msg ' +

        (isUser ? 'sg-chat__msg--user' : isAgent ? 'sg-chat__msg--agent' : 'sg-chat__msg--bot') +

        (extraClass ? ' ' + extraClass : '');

      bubble.textContent = text;

      messagesEl.appendChild(bubble);

      messagesEl.scrollTop = messagesEl.scrollHeight;

      if (!extraClass && !isRestoring) {
        displayThread.push({
          role: isUser ? 'user' : isAgent ? 'agent' : 'assistant',
          content: text,
        });
        if (displayThread.length > MAX_STORED_MESSAGES) {
          displayThread = displayThread.slice(-MAX_STORED_MESSAGES);
        }
        welcomed = true;
        persistState();
      }

      return bubble;

    }



    function setBusy(busy) {

      isSending = busy;

      sendBtn.disabled = busy;

      input.disabled = busy;

    }



    function resizeInput() {

      input.style.height = 'auto';

      input.style.height = Math.min(input.scrollHeight, 96) + 'px';

    }



    function errorText(data, status) {

      if (data && data.err) {

        return data.err;

      }

      if (status === 404) {

        return 'Chat service is not available on this site yet. Please call 1300 225 276.';

      }

      if (status === 429) {

        return 'Too many messages, please wait a moment and try again.';

      }

      return cfg.errorGeneric || 'Something went wrong. Please try again or call us.';

    }



    function removeLeadForm() {

      if (activeLeadForm && activeLeadForm.parentNode) {

        activeLeadForm.parentNode.removeChild(activeLeadForm);

      }

      activeLeadForm = null;

    }



    function createField(id, label, type, required) {

      var wrap = document.createElement('div');

      wrap.className = 'sg-chat__field';



      var lbl = document.createElement('label');

      lbl.className = 'sg-chat__label';

      lbl.setAttribute('for', id);

      lbl.textContent = label;



      var field = document.createElement('input');

      field.className = 'sg-chat__field-input';

      field.id = id;

      field.type = type || 'text';

      field.required = !!required;

      field.autocomplete = type === 'tel' ? 'tel' : 'given-name';



      wrap.appendChild(lbl);

      wrap.appendChild(field);



      return { wrap: wrap, input: field };

    }



    function handleChatData(data) {

      saveConversationKey(data);

      if (data.quoteMode) {
        quoteMode = true;
      }

      if (data.quoteSession && data.quoteSession.resolveToken) {
        quoteSession = data.quoteSession;
      }

      if (data.quoteSubmitted) {
        quoteSubmitted = true;
        quoteSession = null;
        removeQuoteForm();
        persistState();
        if (data.quoteResult && data.quoteResult.message) {
          appendMessage('assistant', data.quoteResult.message);
        } else {
          appendMessage('assistant', cfg.quoteSuccess || 'Thank you, check your email for your portal link.');
        }
        return;
      }

      if (data.ui) {
        renderUi(data.ui);
      } else if (data.handoff || data.status === 'waiting_agent' || data.status === 'agent_active') {
        enterHandoffMode(data.status || 'waiting_agent');
      } else if (data.quoteSession && data.quoteSession.resolveToken) {
        showQuoteVerifyForm(data.quoteSession);
      }

      persistState();
    }



    function renderUi(ui) {

      if (!ui || !ui.type) {

        return;

      }



      if (ui.type === 'agent_handoff' && !leadSubmitted) {
        showHandoffForm(true);
        return;
      }

      if (ui.type === 'handoff_waiting') {
        enterHandoffMode(ui.status || 'waiting_agent');
        return;
      }

      if (ui.type === 'lead_form' && !leadSubmitted) {

        showLeadForm(ui.intent || 'quote', true);

        return;

      }

      if (ui.type === 'quote_chat' && !quoteSubmitted) {
        startQuoteMode(ui.message || 'I need an Ajax alarm quote for my property');
        return;
      }

      if (ui.type === 'quote_verify_form' && ui.resolveToken && !quoteSubmitted) {
        showQuoteVerifyForm({
          resolveToken: ui.resolveToken,
          summary: ui.summary || [],
        });
        return;
      }



      if (ui.type === 'service_picker') {

        showServicePicker();

        return;

      }



      if (ui.type === 'route' && ui.route) {

        showRouteResult(ui.route);

        return;

      }



      if (ui.type === 'links' && Array.isArray(ui.items)) {

        renderLinkActions(ui.items);

      }

    }



    function renderLinkActions(items) {

      var wrap = document.createElement('div');

      wrap.className = 'sg-chat__inline-actions';



      items.forEach(function (item) {

        if (item.action === 'lead_form') {

          var leadBtn = document.createElement('button');

          leadBtn.type = 'button';

          leadBtn.className = 'sg-chat__chip';

          leadBtn.textContent = item.label || 'Request a callback';

          leadBtn.addEventListener('click', function () {

            dismissStarters();

            showLeadForm(item.intent || 'quote');

          });

          wrap.appendChild(leadBtn);

          return;

        }

        if (item.action === 'agent_handoff') {
          var handoffBtn = document.createElement('button');
          handoffBtn.type = 'button';
          handoffBtn.className = 'sg-chat__chip';
          handoffBtn.textContent = item.label || 'Talk to a person';
          handoffBtn.addEventListener('click', function () {
            dismissStarters();
            showHandoffForm(false);
          });
          wrap.appendChild(handoffBtn);
          return;
        }



        if (item.action === 'service_picker') {

          var pickerBtn = document.createElement('button');

          pickerBtn.type = 'button';

          pickerBtn.className = 'sg-chat__chip';

          pickerBtn.textContent = item.label || 'Help me choose';

          pickerBtn.addEventListener('click', function () {

            dismissStarters();

            showServicePicker();

          });

          wrap.appendChild(pickerBtn);

          return;

        }



        if (item.href) {

          var link = document.createElement('a');

          link.className = 'sg-chat__chip sg-chat__chip--link';

          link.href = item.href;

          if (item.href.indexOf('tel:') !== 0) {

            link.target = '_blank';

            link.rel = 'noopener';

          }

          link.textContent = item.label || 'Learn more';

          link.addEventListener('click', function () {

            dismissStarters();

          });

          wrap.appendChild(link);

        }

      });



      if (wrap.childNodes.length) {

        messagesEl.appendChild(wrap);

        messagesEl.scrollTop = messagesEl.scrollHeight;

      }

    }



    function showStarterChoices() {

      if (leadSubmitted || handoffActive) {

        return;

      }

      if (chatAvailable && starterDismissed) {

        return;

      }

      if (activeStarterEl && activeStarterEl.parentNode) {
        return;
      }

      var existingActions = messagesEl.querySelector('.sg-chat__welcome-actions');
      if (existingActions) {
        activeStarterEl = existingActions;
        return;
      }



      var choices = cfg.starterChoices;

      if (!Array.isArray(choices) || !choices.length) {

        choices = chatAvailable
          ? [
              { label: 'Get a quote', action: 'lead_form', intent: 'quote' },
              { label: 'Help me choose', action: 'service_picker' },
              { label: 'Call us', href: cfg.phoneHref || 'tel:1300225276' },
            ]
          : [
              { label: 'Talk to a person', action: 'agent_handoff' },
              { label: 'Call us', href: cfg.phoneHref || 'tel:1300225276' },
            ];

      }



      if (!chatAvailable) {
        choices = choices.filter(function (item) {
          return item && (item.action === 'agent_handoff' || !!item.href);
        });

        if (!choices.length) {
          choices = [
            { label: 'Talk to a person', action: 'agent_handoff' },
            { label: 'Call us', href: cfg.phoneHref || 'tel:1300225276' },
          ];
        }
      }

      var wrap = document.createElement('div');

      wrap.className = 'sg-chat__inline-actions sg-chat__welcome-actions';



      choices.forEach(function (item) {

        if (item.action === 'quote_chat') {

          var quoteBtn = document.createElement('button');

          quoteBtn.type = 'button';

          quoteBtn.className = 'sg-chat__chip sg-chat__chip--primary';

          quoteBtn.textContent = item.label || 'Quote my alarm';

          quoteBtn.addEventListener('click', function () {

            startQuoteMode(item.message || 'I need an Ajax alarm quote for my property');

          });

          wrap.appendChild(quoteBtn);

          return;

        }



        if (item.action === 'lead_form') {

          var leadBtn = document.createElement('button');

          leadBtn.type = 'button';

          leadBtn.className = 'sg-chat__chip sg-chat__chip--primary';

          leadBtn.textContent = item.label || 'Get a quote';

          leadBtn.addEventListener('click', function () {

            dismissStarters();

            appendMessage('user', item.label || 'Get a quote');

            showLeadForm(item.intent || 'quote', true);

          });

          wrap.appendChild(leadBtn);

          return;

        }

        if (item.action === 'agent_handoff') {
          var agentBtn = document.createElement('button');
          agentBtn.type = 'button';
          agentBtn.className = 'sg-chat__chip';
          agentBtn.textContent = item.label || 'Talk to a person';
          agentBtn.addEventListener('click', function () {
            dismissStarters();
            appendMessage('user', item.label || 'Talk to a person');
            showHandoffForm(true);
          });
          wrap.appendChild(agentBtn);
          return;
        }



        if (item.action === 'service_picker') {

          var pickerBtn = document.createElement('button');

          pickerBtn.type = 'button';

          pickerBtn.className = 'sg-chat__chip';

          pickerBtn.textContent = item.label || 'Help me choose';

          pickerBtn.addEventListener('click', function () {

            dismissStarters();

            appendMessage('user', item.label || 'Help me choose');

            appendMessage(

              'assistant',

              cfg.quizPrompt || 'What is your main security need? Pick one below.'

            );

            showServicePicker();

          });

          wrap.appendChild(pickerBtn);

          return;

        }



        if (item.href) {

          var link = document.createElement('a');

          link.className = 'sg-chat__chip sg-chat__chip--link';

          link.href = item.href;

          if (item.href.indexOf('tel:') !== 0) {

            link.target = '_blank';

            link.rel = 'noopener';

          }

          link.textContent = item.label || 'Call us';

          link.addEventListener('click', function () {

            dismissStarters();

          });

          wrap.appendChild(link);

        }

      });



      if (wrap.childNodes.length) {

        messagesEl.appendChild(wrap);

        activeStarterEl = wrap;

        messagesEl.scrollTop = messagesEl.scrollHeight;

      }

    }



    function removeQuoteForm() {
      if (activeQuoteForm && activeQuoteForm.parentNode) {
        activeQuoteForm.parentNode.removeChild(activeQuoteForm);
      }
      activeQuoteForm = null;
    }

    function startQuoteMode(message) {
      if (quoteSubmitted || !chatAvailable) {
        return;
      }
      quoteMode = true;
      dismissStarters();
      sendMessage(message || 'I need an Ajax alarm quote for my property');
    }

    function showQuoteVerifyForm(session) {
      if (quoteSubmitted || !quoteSubmitUrl || !session || !session.resolveToken) {
        return;
      }

      dismissStarters();
      removeQuoteForm();
      quoteSession = session;
      otpVerifiedToken = null;

      var formEl = document.createElement('form');
      formEl.className = 'sg-chat__lead sg-chat__quote-form';
      formEl.setAttribute('novalidate', 'novalidate');

      var intro = document.createElement('p');
      intro.className = 'sg-chat__quote-intro';
      intro.textContent = cfg.quoteIntro || 'Verify your mobile to receive your quote by email and in your portal.';
      formEl.appendChild(intro);

      if (Array.isArray(session.summary) && session.summary.length) {
        var list = document.createElement('ul');
        list.className = 'sg-chat__quote-summary';
        session.summary.forEach(function (row) {
          var li = document.createElement('li');
          li.textContent = (row.qty || 1) + ' × ' + (row.name || 'Device');
          list.appendChild(li);
        });
        formEl.appendChild(list);
      }

      var first = createField('sg-chat-q-first', 'First name', 'text', true);
      first.input.autocomplete = 'given-name';
      var last = createField('sg-chat-q-last', 'Last name', 'text', true);
      last.input.autocomplete = 'family-name';
      var email = createField('sg-chat-q-email', 'Email (optional)', 'email', false);
      email.input.autocomplete = 'email';
      var phone = createField('sg-chat-q-phone', 'Mobile', 'tel', true);
      phone.input.autocomplete = 'tel';

      formEl.appendChild(first.wrap);
      formEl.appendChild(last.wrap);
      formEl.appendChild(email.wrap);
      formEl.appendChild(phone.wrap);

      var otpRow = document.createElement('div');
      otpRow.className = 'sg-chat__otp-row';
      var sendOtpBtn = document.createElement('button');
      sendOtpBtn.type = 'button';
      sendOtpBtn.className = 'sg-chat__chip sg-chat__chip--primary';
      sendOtpBtn.textContent = cfg.otpSendLabel || 'Send code';
      otpRow.appendChild(sendOtpBtn);
      formEl.appendChild(otpRow);

      var otpPanel = document.createElement('div');
      otpPanel.className = 'sg-chat__otp-panel hidden';
      var otpInput = document.createElement('input');
      otpInput.className = 'sg-chat__field-input sg-chat__otp-input';
      otpInput.id = 'sg-chat-q-otp';
      otpInput.inputMode = 'numeric';
      otpInput.maxLength = 6;
      otpInput.placeholder = '6-digit code';
      var verifyOtpBtn = document.createElement('button');
      verifyOtpBtn.type = 'button';
      verifyOtpBtn.className = 'sg-chat__chip';
      verifyOtpBtn.textContent = cfg.otpVerifyLabel || 'Verify';
      otpPanel.appendChild(otpInput);
      otpPanel.appendChild(verifyOtpBtn);
      if (cfg.otpDemo) {
        var otpHint = document.createElement('p');
        otpHint.className = 'sg-chat__otp-hint';
        otpHint.textContent = cfg.otpDemoHint || 'Demo: enter any 6 digits';
        otpPanel.appendChild(otpHint);
      }
      var verifiedMsg = document.createElement('p');
      verifiedMsg.className = 'sg-chat__otp-verified hidden';
      verifiedMsg.textContent = 'Mobile verified';
      formEl.appendChild(otpPanel);
      formEl.appendChild(verifiedMsg);

      var consentWrap = document.createElement('label');
      consentWrap.className = 'sg-chat__consent';
      var consentBox = document.createElement('input');
      consentBox.type = 'checkbox';
      consentBox.id = 'sg-chat-q-consent';
      consentBox.required = true;
      consentWrap.appendChild(consentBox);
      consentWrap.appendChild(document.createTextNode(' I agree to be contacted about my quote.'));
      formEl.appendChild(consentWrap);

      var submit = document.createElement('button');
      submit.type = 'submit';
      submit.className = 'sg-chat__lead-submit';
      submit.textContent = cfg.quoteSubmit || 'Send my quote';
      submit.disabled = true;
      formEl.appendChild(submit);

      sendOtpBtn.addEventListener('click', function () {
        if (!otpSendUrl || !phone.input.value.trim()) {
          appendMessage('assistant', 'Enter your mobile number first.');
          return;
        }
        sendOtpBtn.disabled = true;
        fetch(otpSendUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ phone: phone.input.value.trim() }),
        })
          .then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (data) {
              if (!res.ok || !data.ok) {
                appendMessage('assistant', (data && data.err) || 'Could not send code.');
                return;
              }
              otpPanel.classList.remove('hidden');
              otpInput.focus();
            });
          })
          .catch(function () {
            appendMessage('assistant', 'Could not send verification code.');
          })
          .finally(function () {
            sendOtpBtn.disabled = false;
          });
      });

      verifyOtpBtn.addEventListener('click', function () {
        if (!otpVerifyUrl) {
          return;
        }
        fetch(otpVerifyUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            phone: phone.input.value.trim(),
            code: otpInput.value.trim(),
          }),
        })
          .then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (data) {
              if (!res.ok || !data.ok || !data.otpVerifiedToken) {
                appendMessage('assistant', (data && data.err) || 'Invalid code.');
                return;
              }
              otpVerifiedToken = data.otpVerifiedToken;
              otpPanel.classList.add('hidden');
              verifiedMsg.classList.remove('hidden');
              phone.input.disabled = true;
              submit.disabled = !consentBox.checked;
            });
          })
          .catch(function () {
            appendMessage('assistant', 'Verification failed.');
          });
      });

      consentBox.addEventListener('change', function () {
        submit.disabled = !consentBox.checked || !otpVerifiedToken;
      });

      formEl.addEventListener('submit', function (event) {
        event.preventDefault();
        if (isSending || quoteSubmitted || !otpVerifiedToken) {
          appendMessage('assistant', 'Please verify your mobile first.');
          return;
        }
        if (!consentBox.checked) {
          return;
        }
        setBusy(true);
        submit.disabled = true;
        fetch(quoteSubmitUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(withConversation({
            firstName: first.input.value.trim(),
            lastName: last.input.value.trim(),
            email: email.input.value.trim(),
            phone: phone.input.value.trim(),
            resolveToken: session.resolveToken,
            otpVerifiedToken: otpVerifiedToken,
            transcript: history,
          })),
        })
          .then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (data) {
              if (!res.ok || !data.ok) {
                appendMessage('assistant', (data && data.err) || cfg.quoteError || errorText(data, res.status));
                submit.disabled = false;
                return;
              }
              quoteSubmitted = true;
              quoteMode = false;
              quoteSession = null;
              removeQuoteForm();
              saveConversationKey(data);
              appendMessage('assistant', data.message || cfg.quoteSuccess || 'Thank you, check your email.');
              persistState();
            });
          })
          .catch(function () {
            appendMessage('assistant', cfg.quoteError || 'Could not send quote.');
            submit.disabled = false;
          })
          .finally(function () {
            setBusy(false);
          });
      });

      messagesEl.appendChild(formEl);
      activeQuoteForm = formEl;
      messagesEl.scrollTop = messagesEl.scrollHeight;
      first.input.focus();
    }

    function enterHandoffMode(status) {
      handoffActive = true;
      handoffStatus = status || 'waiting_agent';
      leadSubmitted = true;
      startHandoffPolling();
      persistState();
    }

    function stopHandoffPolling() {
      if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
      }
    }

    function startHandoffPolling() {
      if (pollTimer) {
        return;
      }
      if (!pollUrl && !apiUrl) {
        return;
      }
      pollHandoffOnce();
      pollTimer = setInterval(pollHandoffOnce, 3000);
    }

    function pollHandoffOnce() {
      var url = pollUrl || (apiUrl.replace(/\/chat\/?$/, '/chat/poll'));
      if (!url || !getConversationKey()) {
        return;
      }

      fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(
          withConversation({
            afterId: lastPollMessageId,
            pageContext: pageContext,
          })
        ),
      })
        .then(function (res) {
          return res.json().catch(function () {
            return {};
          });
        })
        .then(function (data) {
          if (!data || !data.ok) {
            return;
          }

          var prevStatus = handoffStatus;
          if (data.status) {
            handoffStatus = data.status;
          }

          var msgs = Array.isArray(data.messages) ? data.messages : [];
          msgs.forEach(function (msg) {
            var id = Number(msg.id) || 0;
            if (id && seenPollMessageIds[id]) {
              return;
            }
            if (id) {
              seenPollMessageIds[id] = true;
              if (id > lastPollMessageId) {
                lastPollMessageId = id;
              }
            }

            if (msg.role === 'agent') {
              appendMessage('agent', msg.content);
            } else if (msg.role === 'system') {
              appendMessage('assistant', msg.content, 'sg-chat__msg--system');
            }
          });

          if (data.status === 'resolved') {
            if (prevStatus !== 'resolved' && handoffActive) {
              appendMessage(
                'assistant',
                cfg.handoffResolved ||
                  'This chat has been closed by our team. You can keep chatting with the assistant or call 1300 225 276.'
              );
            }
            handoffActive = false;
            leadSubmitted = false;
            stopHandoffPolling();
            ensureAgentEscapeHatch();
          } else if (data.status === 'timed_out') {
            // Server system message already explains follow-up; avoid duplicate canned copy.
            handoffActive = false;
            leadSubmitted = false;
            stopHandoffPolling();
            ensureAgentEscapeHatch();
          } else if (data.status === 'open') {
            if (prevStatus !== 'open' && handoffActive) {
              appendMessage(
                'assistant',
                cfg.handoffReturned ||
                  'You are back with the assistant. How else can we help?'
              );
            }
            handoffActive = false;
            leadSubmitted = false;
            stopHandoffPolling();
            ensureAgentEscapeHatch();
          }

          persistState();
        })
        .catch(function () {
          /* ignore transient poll errors */
        });
    }

    function showHandoffForm(skipIntro) {
      if (leadSubmitted || !leadUrl) {
        if (!leadUrl) {
          window.location.href = cfg.quoteUrl || '/get-an-instant-quote/';
        }
        return;
      }

      dismissStarters();
      removeLeadForm();

      var escapeEl = messagesEl.querySelector('.sg-chat__agent-escape');
      if (escapeEl && escapeEl.parentNode) {
        escapeEl.parentNode.removeChild(escapeEl);
      }

      if (!skipIntro) {
        appendMessage(
          'assistant',
          cfg.handoffIntro ||
            'Sure, I can connect you with our team. Please share your name, email, address, and phone below.'
        );
      }

      var formEl = document.createElement('form');
      formEl.className = 'sg-chat__lead sg-chat__handoff-form';
      formEl.setAttribute('novalidate', 'novalidate');

      var heading = document.createElement('p');
      heading.className = 'sg-chat__handoff-title';
      heading.textContent = 'Connect with our team';
      formEl.appendChild(heading);

      function createHandoffField(id, labelText, type, required, autocomplete) {
        var wrap = document.createElement('div');
        wrap.className = 'sg-chat__field';

        var label = document.createElement('label');
        label.className = 'sg-chat__label';
        label.setAttribute('for', id);
        label.textContent = labelText;

        var inputEl = document.createElement('input');
        inputEl.className = 'sg-chat__field-input';
        inputEl.id = id;
        inputEl.name = id;
        inputEl.type = type || 'text';
        inputEl.required = !!required;
        if (autocomplete) {
          inputEl.autocomplete = autocomplete;
        }

        wrap.appendChild(label);
        wrap.appendChild(inputEl);
        return { wrap: wrap, input: inputEl };
      }

      var first = createHandoffField('sg-chat-h-first', 'First name', 'text', true, 'given-name');
      var last = createHandoffField('sg-chat-h-last', 'Last name', 'text', true, 'family-name');
      var email = createHandoffField('sg-chat-h-email', 'Email', 'email', true, 'email');
      var address = createHandoffField('sg-chat-h-address', 'Address', 'text', true, 'street-address');
      var phone = createHandoffField('sg-chat-h-phone', 'Mobile phone', 'tel', true, 'tel');

      var submit = document.createElement('button');
      submit.type = 'submit';
      submit.className = 'sg-chat__lead-submit';
      submit.textContent = cfg.handoffSubmit || 'Connect me to the team';

      formEl.appendChild(first.wrap);
      formEl.appendChild(last.wrap);
      formEl.appendChild(email.wrap);
      formEl.appendChild(address.wrap);
      formEl.appendChild(phone.wrap);
      formEl.appendChild(submit);

      formEl.addEventListener('submit', function (event) {
        event.preventDefault();
        event.stopPropagation();

        if (isSending || leadSubmitted) {
          return;
        }

        if (
          !first.input.value.trim() ||
          !last.input.value.trim() ||
          !email.input.value.trim() ||
          !address.input.value.trim() ||
          !phone.input.value.trim()
        ) {
          appendMessage('assistant', 'Please fill in all fields.');
          return;
        }

        var phoneDigits = phone.input.value.replace(/\D+/g, '');
        if (phoneDigits.length <= 9) {
          appendMessage('assistant', 'Please enter a phone number with more than 9 digits.');
          return;
        }

        setBusy(true);
        submit.disabled = true;

        fetch(leadUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(
            withConversation({
              intent: 'agent_handoff',
              firstName: first.input.value.trim(),
              lastName: last.input.value.trim(),
              email: email.input.value.trim(),
              address: address.input.value.trim(),
              phone: phone.input.value.trim(),
              pagePath: pageContext.path || window.location.pathname,
              pageTitle: pageContext.title || document.title,
              transcript: history,
            })
          ),
        })
          .then(function (res) {
            return res
              .json()
              .catch(function () {
                return {};
              })
              .then(function (data) {
                if (!res.ok || !data.ok) {
                  appendMessage(
                    'assistant',
                    (data && data.err) || cfg.handoffError || cfg.leadError || errorText(data, res.status)
                  );
                  submit.disabled = false;
                  return;
                }

                leadSubmitted = true;
                removeLeadForm();
                saveConversationKey(data);
                appendMessage(
                  'assistant',
                  data.message ||
                    cfg.handoffSuccess ||
                    'Thanks, connecting you with our team now…'
                );
                enterHandoffMode(data.status || 'waiting_agent');
                if (data.ui) {
                  renderUi(data.ui);
                }
                persistState();
              });
          })
          .catch(function () {
            appendMessage(
              'assistant',
              cfg.handoffError || cfg.leadError || 'Could not send your details. Please call 1300 225 276.'
            );
            submit.disabled = false;
          })
          .finally(function () {
            setBusy(false);
          });
      });

      messagesEl.appendChild(formEl);
      activeLeadForm = formEl;
      messagesEl.scrollTop = messagesEl.scrollHeight;
      first.input.focus();
    }

    function showLeadForm(intent, skipIntro) {

      if (leadSubmitted || !leadUrl) {

        if (!leadUrl) {

          window.location.href = cfg.quoteUrl || '/get-an-instant-quote/';

        }

        return;

      }



      dismissStarters();

      removeLeadForm();



      var resolvedIntent = intent || pageContext.service || 'quote';

      if (resolvedIntent === 'general') {

        resolvedIntent = 'quote';

      }



      if (!skipIntro) {

        appendMessage(

          'assistant',

          cfg.leadIntro || 'Leave your details and our team will call you back.'

        );

      }



      var formEl = document.createElement('form');

      formEl.className = 'sg-chat__lead';

      formEl.setAttribute('novalidate', 'novalidate');



      var propertyField = document.createElement('div');

      propertyField.className = 'sg-chat__field';

      var propertyLabel = document.createElement('label');

      propertyLabel.className = 'sg-chat__label';

      propertyLabel.setAttribute('for', 'sg-chat-property');

      propertyLabel.textContent = 'Property type';

      var propertySelect = document.createElement('select');

      propertySelect.className = 'sg-chat__field-input';

      propertySelect.id = 'sg-chat-property';

      propertySelect.required = true;

      [

        ['home', 'Home'],

        ['apartment', 'Apartment'],

        ['business', 'Business'],

        ['other', 'Other'],

      ].forEach(function (opt) {

        var option = document.createElement('option');

        option.value = opt[0];

        option.textContent = opt[1];

        propertySelect.appendChild(option);

      });

      propertyField.appendChild(propertyLabel);

      propertyField.appendChild(propertySelect);



      var suburb = createField('sg-chat-suburb', 'Suburb', 'text', true);

      var first = createField('sg-chat-first', 'First name', 'text', true);

      var last = createField('sg-chat-last', 'Last name', 'text', true);

      var phone = createField('sg-chat-phone', 'Mobile phone', 'tel', true);

      phone.input.autocomplete = 'tel';



      var submit = document.createElement('button');

      submit.type = 'submit';

      submit.className = 'sg-chat__lead-submit';

      submit.textContent = cfg.leadSubmit || 'Send my details';



      formEl.appendChild(propertyField);

      formEl.appendChild(suburb.wrap);

      formEl.appendChild(first.wrap);

      formEl.appendChild(last.wrap);

      formEl.appendChild(phone.wrap);

      formEl.appendChild(submit);



      formEl.addEventListener('submit', function (event) {

        event.preventDefault();

        event.stopPropagation();



        if (isSending || leadSubmitted) {

          return;

        }



        if (

          !propertySelect.value ||

          !suburb.input.value.trim() ||

          !first.input.value.trim() ||

          !last.input.value.trim() ||

          !phone.input.value.trim()

        ) {

          appendMessage('assistant', 'Please fill in all fields.');

          return;

        }



        setBusy(true);

        submit.disabled = true;



        fetch(leadUrl, {

          method: 'POST',

          credentials: 'same-origin',

          headers: { 'Content-Type': 'application/json' },

          body: JSON.stringify(

            withConversation({

              intent: resolvedIntent,

              propertyType: propertySelect.value,

              suburb: suburb.input.value.trim(),

              firstName: first.input.value.trim(),

              lastName: last.input.value.trim(),

              phone: phone.input.value.trim(),

              pagePath: pageContext.path || window.location.pathname,

              pageTitle: pageContext.title || document.title,

              transcript: history,

            })

          ),

        })

          .then(function (res) {

            return res

              .json()

              .catch(function () {

                return {};

              })

              .then(function (data) {

                if (!res.ok || !data.ok) {

                  appendMessage(

                    'assistant',

                    (data && data.err) || cfg.leadError || errorText(data, res.status)

                  );

                  submit.disabled = false;

                  return;

                }



                leadSubmitted = true;

                removeLeadForm();

                saveConversationKey(data);

                appendMessage(

                  'assistant',

                  data.message || cfg.leadSuccess || 'Thanks, our team will call you shortly.'

                );

                appendPostSubmitHelp();

                persistState();

              });

          })

          .catch(function () {

            appendMessage(

              'assistant',

              cfg.leadError || 'Could not send your details. Please call 1300 225 276.'

            );

            submit.disabled = false;

          })

          .finally(function () {

            setBusy(false);

          });

      });



      messagesEl.appendChild(formEl);

      activeLeadForm = formEl;

      messagesEl.scrollTop = messagesEl.scrollHeight;

      suburb.input.focus();

    }



    function sendMessage(text) {

      if (!text || isSending) {

        return;

      }



      appendMessage('user', text);



      // Live-agent handoff must keep working even when DeepSeek is offline.
      // Previously !chatAvailable short-circuited here, so visitor messages never
      // reached WordPress / the admin inbox.
      if (!chatAvailable && !handoffActive) {

        ensureHandoffChoicesVisible();

        if (!activeStarterEl && !activeLeadForm) {

          appendMessage(

            'assistant',

            cfg.handoffIntro ||

              'Tap Talk to a person below and our team will help you live.'

          );

          ensureHandoffChoicesVisible();

        }

        return;

      }



      dismissStarters();



      history.push({ role: 'user', content: text });

      persistState();

      var typing = appendThinking();

      setBusy(true);



      var payload = withConversation({

        messages: history,

        quoteMode: quoteMode,

        quoteSession: quoteSession,

        clientState: {

          leadSubmitted: leadSubmitted,

          quoteSubmitted: quoteSubmitted,

          quoteMode: quoteMode,

          handoffActive: handoffActive,

        },

      });



      fetch(apiUrl, {

        method: 'POST',

        credentials: 'same-origin',

        headers: { 'Content-Type': 'application/json' },

        body: JSON.stringify(payload),

      })

        .then(function (res) {

          return res

            .json()

            .catch(function () {

              return {};

            })

            .then(function (data) {

              typing.remove();



              if (!res.ok || !data.ok) {

                appendMessage('assistant', errorText(data, res.status));

                history.pop();

                return;

              }

              // During handoff the API returns a canned ack every time. Do not
              // spam it into the thread; the user already sees their own bubble.
              var inHandoff =
                !!data.handoff ||
                data.status === 'waiting_agent' ||
                data.status === 'agent_active' ||
                handoffActive;

              if (inHandoff) {
                handleChatData(data);
                persistState();
                return;
              }

              if (!data.reply) {
                appendMessage('assistant', errorText(data, res.status));
                history.pop();
                return;
              }

              appendMessage('assistant', data.reply);

              history.push({ role: 'assistant', content: data.reply });

              handleChatData(data);

              persistState();

            });

        })

        .catch(function () {

          typing.remove();

          appendMessage(

            'assistant',

            cfg.errorGeneric || 'Something went wrong. Please try again or call us.'

          );

          history.pop();

        })

        .finally(function () {

          setBusy(false);

          resizeInput();

        });

    }



    function showRouteResult(data) {

      if (!data || !data.ok) {

        appendMessage('assistant', cfg.errorGeneric || 'Could not recommend a service.');

        return;

      }



      if (data.reason) {

        appendMessage('assistant', data.reason);

      }



      var card = document.createElement('div');

      card.className = 'sg-chat__route-card';



      var title = document.createElement('p');

      title.className = 'sg-chat__quote-title';

      title.textContent = data.label || 'Recommended';



      var link = document.createElement('a');

      link.className = 'sg-chat__route-link';

      link.href = data.url || cfg.quoteUrl || '/';

      link.target = '_blank';

      link.rel = 'noopener';

      link.textContent = 'Learn more about ' + (data.label || 'this service');



      card.appendChild(title);

      card.appendChild(link);



      var actions = document.createElement('div');

      actions.className = 'sg-chat__inline-actions';



      if (data.recommendation === 'alarms' && cfg.quoteUrl) {

        var onlineBtn = document.createElement('a');

        onlineBtn.className = 'sg-chat__chip sg-chat__chip--primary sg-chat__chip--link';

        onlineBtn.href = cfg.quoteUrl;

        onlineBtn.target = '_blank';

        onlineBtn.rel = 'noopener';

        onlineBtn.textContent = 'Instant quote online';

        actions.appendChild(onlineBtn);

      }



      if (!leadSubmitted) {

        var callbackBtn = document.createElement('button');

        callbackBtn.type = 'button';

        callbackBtn.className = 'sg-chat__chip';

        callbackBtn.textContent = 'Request a callback';

        callbackBtn.addEventListener('click', function () {

          showLeadForm(data.recommendation || 'quote');

        });

        actions.appendChild(callbackBtn);

      }



      card.appendChild(actions);

      messagesEl.appendChild(card);

      messagesEl.scrollTop = messagesEl.scrollHeight;

    }



    function showServicePicker() {

      dismissStarters();



      var wrap = document.createElement('div');

      wrap.className = 'sg-chat__inline-actions';



      var options = [

        { key: 'alarms', label: 'Alarm / intrusion', concern: 'alarms' },

        { key: 'cctv', label: 'Cameras / CCTV', concern: 'cctv' },

        { key: 'access_control', label: 'Door access', concern: 'access_control' },

        { key: 'intercom', label: 'Intercom / doorbell', concern: 'intercom' },

      ];



      options.forEach(function (opt) {

        var btn = document.createElement('button');

        btn.type = 'button';

        btn.className = 'sg-chat__chip';

        btn.textContent = opt.label;

        btn.addEventListener('click', function () {

          if (!routeUrl || isSending) {

            return;

          }



          appendMessage('user', opt.label);

          setBusy(true);



          fetch(routeUrl, {

            method: 'POST',

            credentials: 'same-origin',

            headers: { 'Content-Type': 'application/json' },

            body: JSON.stringify(

              withConversation({

                answers: { service: opt.key, concern: opt.concern },

              })

            ),

          })

            .then(function (res) {

              return res

                .json()

                .catch(function () {

                  return {};

                })

                .then(function (data) {

                  saveConversationKey(data);

                  if (data.route) {

                    showRouteResult(data.route);

                  } else {

                    showRouteResult(data);

                  }

                });

            })

            .catch(function () {

              appendMessage('assistant', cfg.errorGeneric || 'Something went wrong.');

            })

            .finally(function () {

              setBusy(false);

            });

        });

        wrap.appendChild(btn);

      });



      messagesEl.appendChild(wrap);

      messagesEl.scrollTop = messagesEl.scrollHeight;

    }



    toggle.addEventListener('click', function (event) {

      event.preventDefault();

      event.stopPropagation();

      setOpen(!isOpen);

    });



    closeBtn.addEventListener('click', function (event) {

      event.preventDefault();

      event.stopPropagation();

      setOpen(false);

    });

    if (newChatBtn) {
      newChatBtn.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        startFreshConversation();
      });
    }



    form.addEventListener('submit', function (event) {

      event.preventDefault();

      event.stopPropagation();

      var text = (input.value || '').trim();

      if (!text) {

        return;

      }

      input.value = '';

      resizeInput();

      sendMessage(text);

    });



    sendBtn.addEventListener('click', function (event) {

      event.preventDefault();

      event.stopPropagation();

      var text = (input.value || '').trim();

      if (!text) {

        return;

      }

      input.value = '';

      resizeInput();

      sendMessage(text);

    });



    input.addEventListener('input', resizeInput);



    input.addEventListener('keydown', function (event) {

      if (event.key === 'Enter' && !event.shiftKey) {

        event.preventDefault();

        var text = (input.value || '').trim();

        if (!text) {

          return;

        }

        input.value = '';

        resizeInput();

        sendMessage(text);

      }

    });



    document.addEventListener('keydown', function (event) {

      if (event.key === 'Escape' && isOpen) {

        setOpen(false);

      }

    });



    restoreThreadFromStorage();

    window.addEventListener('pagehide', persistState);

    setOpen(false);

  }



  if (document.readyState === 'loading') {

    document.addEventListener('DOMContentLoaded', init);

  } else {

    init();

  }

})();

