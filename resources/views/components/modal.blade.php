@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidthValues = [
    'sm' => '24rem',
    'md' => '28rem',
    'lg' => '32rem',
    'xl' => '36rem',
    '2xl' => '42rem',
];
$maxWidthValue = $maxWidthValues[$maxWidth];
@endphp

<div x-data="{ show: @js($show) }"
     x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
     x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
     x-on:close.stop="show = false"
     x-on:keydown.escape.window="show = false"
     x-show="show"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 50; overflow-y: auto; display: flex; min-height: 100vh; align-items: center; justify-content: center; padding: 1rem;">

    <!-- Simple backdrop -->
    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-on:click="show = false"
         style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5);">
    </div>

    <!-- Modal content -->
    <div x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 transform scale-95 translate-y-4"
         @click.stop
         style="position: relative; width: 100%; max-width: {{ $maxWidthValue }}; margin: auto; background: white; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden; z-index: 51;">

        <!-- Close button -->
        <button x-on:click="show = false"
                style="position: absolute; top: 1rem; right: 1rem; z-index: 10; width: 2rem; height: 2rem; display: flex; align-items: center; justify-content: center; background: #f3f4f6; border: none; border-radius: 6px; color: #6b7280; cursor: pointer; transition: all 0.2s ease;"
                onmouseover="this.style.background='#e5e7eb'; this.style.color='#374151'"
                onmouseout="this.style.background='#f3f4f6'; this.style.color='#6b7280'">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>

        <!-- Content -->
        {{ $slot }}
    </div>
</div>

<style>
/* Clean modal animations */
.modal-content {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(1rem);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Responsive design */
@media (max-width: 640px) {
    .modal-overlay {
        padding: 0.5rem;
    }
}
</style>
