@if(config('openproject-feedback.widget.enabled', true))
    @if(!config('openproject-feedback.widget.show_only_authenticated', true) || auth()->check())
        @if(config('openproject-feedback.widget.show_only_authenticated', true) && auth()->check())
            <script>
                (function() {
                    function addAuthClass() {
                        if (document.body) {
                            document.body.classList.add('authenticated');
                            document.body.setAttribute('data-user-id', '{{ auth()->id() }}');
                        } else {
                            setTimeout(addAuthClass, 10);
                        }
                    }
                    addAuthClass();
                })();
            </script>
        @endif

        @push('scripts')
            <script>
                window.OpenProjectFeedbackConfig = {
                    route: '{{ route('openproject-feedback.store') }}',
                    position: '{{ config('openproject-feedback.widget.position', 'bottom-left') }}',
                    offset: {
                        bottom: {{ config('openproject-feedback.widget.offset.bottom', 64) }},
                        top: {{ config('openproject-feedback.widget.offset.top', 16) }},
                        left: {{ config('openproject-feedback.widget.offset.left', 0) }},
                        right: {{ config('openproject-feedback.widget.offset.right', 16) }},
                    },
                    zIndex: {{ config('openproject-feedback.widget.z_index', 50) }},
                    colors: {
                        primary: '{{ config('openproject-feedback.widget.color.primary', '#3b82f6') }}',
                        hover: '{{ config('openproject-feedback.widget.color.hover', '#2563eb') }}',
                    },
                    text: '{{ config('openproject-feedback.widget.text', 'FEEDBACK') }}',
                    showOnlyAuthenticated: {{ config('openproject-feedback.widget.show_only_authenticated', true) ? 'true' : 'false' }},
                };
            </script>
            @vite(['resources/js/vendor/openproject-feedback/feedback-widget.js'])
        @endpush
    @endif
@endif

