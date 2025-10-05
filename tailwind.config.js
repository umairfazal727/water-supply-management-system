import preset from './vendor/filament/support/tailwind.config.preset'
 
export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './resources/views/livewire/*.blade.php',
        './resources/js/*.js',
        './vendor/filament/**/*.blade.php',
    ],
}