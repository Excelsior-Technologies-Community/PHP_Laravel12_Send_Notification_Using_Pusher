/**
 * Laravel Echo + Pusher configuration file
 *
 * This file is responsible for connecting your Laravel application
 * to the Pusher WebSocket server so that it can receive realtime
 * broadcasted events (like PostCreate event).
 */

import Echo from 'laravel-echo';   // Echo: Laravel's realtime event listener
import Pusher from 'pusher-js';     // Pusher JS client library

// Make Pusher globally available
window.Pusher = Pusher;

/**
 * Create a new Echo instance.
 *
 * This tells Echo to use Pusher as the broadcaster.
 * All broadcasting settings are loaded from your .env file (Vite env vars).
 */
window.Echo = new Echo({
    broadcaster: 'pusher',                         // Use Pusher as WebSocket provider

    // Public key for connecting to your Pusher app
    key: import.meta.env.VITE_PUSHER_APP_KEY,

    // The Pusher cluster (e.g., ap2)
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,

    /**
     * forceTLS ensures that WebSocket communication uses the secure
     * WSS protocol. Since Pusher uses HTTPS/WSS on production by default,
     * this MUST be true for cluster-based connections.
     */
    forceTLS: true,

    /**
     * Custom WebSocket host configuration.
     *
     * If VITE_PUSHER_HOST is provided, Echo will use that.
     * Otherwise, it falls back to Pusher's default host structure:
     * ws-{cluster}.pusher.com
     *
     * Example: ws-ap2.pusher.com
     */
    wsHost: import.meta.env.VITE_PUSHER_HOST 
        || `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,

    /**
     * Ports for WebSocket communication.
     * 443 = standard HTTPS/WSS port used by Pusher.
     */
    wsPort: import.meta.env.VITE_PUSHER_PORT || 443,
    wssPort: import.meta.env.VITE_PUSHER_PORT || 443,

    /**
     * Choose which WebSocket transports are allowed.
     * 'ws'  = normal WebSockets
     * 'wss' = secure WebSockets
     *
     * Enabling both increases compatibility.
     */
    enabledTransports: ['ws', 'wss'],
});
