{{-- Floating messages after an action. --}}
<div class="toast-stack d-print-none">
    @if (session('success'))
        <div class="app-toast toast-success" data-autohide="6000">
            <i class="mdi mdi-check-circle toast-icon"></i>
            <div class="toast-text">{{ session('success') }}</div>
            <button type="button" class="toast-close" aria-label="Close">&times;</button>
        </div>
    @endif

    @if (session('error'))
        <div class="app-toast toast-error">
            <i class="mdi mdi-alert-circle toast-icon"></i>
            <div class="toast-text">{{ session('error') }}</div>
            <button type="button" class="toast-close" aria-label="Close">&times;</button>
        </div>
    @endif
</div>
