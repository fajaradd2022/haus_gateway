{{-- Chat view (header, search, messages, footer) --}}
<div class="pane-chat" id="paneChat" hidden>
    @include('helpdesk.partials.chat-pane.header')
    @include('helpdesk.partials.chat-pane.search-bar')
    @include('helpdesk.partials.chat-pane.messages')
    @include('helpdesk.partials.chat-pane.footer')
</div>
