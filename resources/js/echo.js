import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',

    key: import.meta.env.VITE_PUSHER_APP_KEY,

    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,

    forceTLS: true,

    enabledTransports: ['ws', 'wss'],
});

console.log('Echo initialized:', window.Echo);
console.log(
    'Pusher key:',
    import.meta.env.VITE_PUSHER_APP_KEY
);
console.log(
    'Pusher cluster:',
    import.meta.env.VITE_PUSHER_APP_CLUSTER
);