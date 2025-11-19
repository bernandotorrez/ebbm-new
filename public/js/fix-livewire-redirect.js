// Fix for Livewire 404 after redirect and file upload
document.addEventListener('DOMContentLoaded', function() {
    if (window.Livewire) {
        let hasRedirected = false;
        let redirectTimeout = null;
        
        // Hook into Livewire lifecycle
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            // Track successful commits that trigger redirects
            succeed(({ snapshot, effect }) => {
                if (effect.redirect) {
                    hasRedirected = true;
                    console.log('Redirect detected, suppressing subsequent errors');
                    
                    // Clear flag after redirect completes
                    if (redirectTimeout) clearTimeout(redirectTimeout);
                    redirectTimeout = setTimeout(() => {
                        hasRedirected = false;
                    }, 2000);
                }
            });
        });
        
        // Intercept and suppress 404 errors after redirect
        Livewire.hook('request', ({ uri, options, payload, respond, succeed, fail }) => {
            fail(({ status, content, preventDefault }) => {
                // Suppress 404 errors that occur after a redirect
                if (hasRedirected && status === 404) {
                    console.log('Suppressing 404 error after redirect');
                    preventDefault();
                    return;
                }
            });
        });
        
        // Also handle message.failed for older Livewire versions
        if (Livewire.hook.available('message.failed')) {
            Livewire.hook('message.failed', (message, component) => {
                if (hasRedirected && message.status === 404) {
                    console.log('Suppressing 404 via message.failed hook');
                    return false;
                }
            });
        }
        
        // Reset on navigation
        document.addEventListener('livewire:navigated', () => {
            hasRedirected = false;
            if (redirectTimeout) clearTimeout(redirectTimeout);
        });
    }
});
