import './bootstrap';
import './echo';

/*
|--------------------------------------------------------------------------
| Notification State
|--------------------------------------------------------------------------
*/

let unreadNotificationCount = 0;


/*
|--------------------------------------------------------------------------
| Notification Sound
|--------------------------------------------------------------------------
*/

function playNotificationSound() {
    try {
        const AudioContext =
            window.AudioContext || window.webkitAudioContext;

        if (!AudioContext) {
            return;
        }

        const ctx = new AudioContext();

        const oscillator = ctx.createOscillator();
        const gainNode = ctx.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(ctx.destination);

        oscillator.frequency.value = 800;
        oscillator.type = 'sine';

        gainNode.gain.setValueAtTime(
            0.3,
            ctx.currentTime
        );

        gainNode.gain.exponentialRampToValueAtTime(
            0.01,
            ctx.currentTime + 0.3
        );

        oscillator.start(ctx.currentTime);

        oscillator.stop(
            ctx.currentTime + 0.3
        );

    } catch (error) {
        console.error(
            'Audio play failed:',
            error
        );
    }
}


/*
|--------------------------------------------------------------------------
| Get Current User ID
|--------------------------------------------------------------------------
*/

function getCurrentUserId() {
    return document.querySelector(
        'meta[name="auth-user-id"]'
    )?.content;
}


/*
|--------------------------------------------------------------------------
| Update Notification Badge
|--------------------------------------------------------------------------
*/

function updateBadge(count) {

    const badge =
        document.getElementById(
            'notification-badge'
        );

    if (!badge) {
        return;
    }

    unreadNotificationCount =
        Math.max(
            0,
            Number(count) || 0
        );

    badge.textContent =
        unreadNotificationCount;

    if (unreadNotificationCount > 0) {

        badge.classList.remove(
            'd-none'
        );

    } else {

        badge.classList.add(
            'd-none'
        );
    }
}


/*
|--------------------------------------------------------------------------
| Increase Badge
|--------------------------------------------------------------------------
*/

function incrementBadge() {

    updateBadge(
        unreadNotificationCount + 1
    );
}


/*
|--------------------------------------------------------------------------
| Decrease Badge
|--------------------------------------------------------------------------
*/

function decrementBadge() {

    updateBadge(
        unreadNotificationCount - 1
    );
}


/*
|--------------------------------------------------------------------------
| Load Unread Notification Count
|--------------------------------------------------------------------------
*/

async function loadNotifications() {

    try {

        const response =
            await fetch(
                '/notifications/unread-count',
                {
                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest',

                        'Accept':
                            'application/json',
                    },
                }
            );

        if (!response.ok) {

            throw new Error(
                'Failed to load notification count.'
            );
        }

        const data =
            await response.json();

        updateBadge(
            data.count
        );

    } catch (error) {

        console.error(
            'Failed to load notification count:',
            error
        );
    }
}


/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

function escapeHtml(value) {

    const div =
        document.createElement('div');

    div.textContent =
        value ?? '';

    return div.innerHTML;
}


/*
|--------------------------------------------------------------------------
| Add Realtime Notification To Dropdown
|--------------------------------------------------------------------------
*/

function addRealtimeNotificationToDropdown(
    notification
) {

    const list =
        document.getElementById(
            'notification-list'
        );

    if (!list) {
        return;
    }

    const message =
        notification.data?.message ||
        'New notification received';

    const type =
        notification.type ||
        'notification';

    const senderName =
        notification.data?.sender_name ||
        'System';

    const createdAt =
        notification.created_at ||
        'Just now';


    /*
    |--------------------------------------------------------------------------
    | Remove Loading / Empty Message
    |--------------------------------------------------------------------------
    */

    const emptyMessage =
        list.querySelector(
            '[data-empty-notification]'
        );

    if (emptyMessage) {
        emptyMessage.remove();
    }


    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Notification
    |--------------------------------------------------------------------------
    */

    const existingNotification =
        list.querySelector(
            `[data-notification-id="${notification.id}"]`
        );

    if (existingNotification) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Create Notification Item
    |--------------------------------------------------------------------------
    */

    const item =
        document.createElement('div');

    item.className =
        'dropdown-item-text small px-3 py-2 border-bottom bg-light';

    item.setAttribute(
        'data-notification-id',
        notification.id
    );

    item.innerHTML = `
        <div class="d-flex justify-content-between align-items-start">

            <div class="me-2">

                <div class="fw-bold">

                    <i
                        class="fa fa-circle text-primary me-1"
                        style="font-size: 7px;"
                    ></i>

                    ${escapeHtml(message)}

                </div>

                <div class="text-muted mt-1">

                    ${escapeHtml(type)}
                    •
                    ${escapeHtml(senderName)}

                </div>

                <div class="text-muted">

                    ${escapeHtml(createdAt)}

                </div>

            </div>

            <span class="badge bg-warning text-dark">
                Unread
            </span>

        </div>
    `;


    /*
    |--------------------------------------------------------------------------
    | Add New Notification To Top
    |--------------------------------------------------------------------------
    */

    list.prepend(item);
}


/*
|--------------------------------------------------------------------------
| Load Notification Dropdown
|--------------------------------------------------------------------------
*/

async function loadDropdownNotifications() {

    const list =
        document.getElementById(
            'notification-list'
        );

    if (!list) {
        return;
    }

    try {

        /*
        |--------------------------------------------------------------------------
        | Show Loading State
        |--------------------------------------------------------------------------
        */

        list.innerHTML = `
            <div
                class="px-3 py-3 text-muted small text-center"
                data-loading-notification
            >
                <i class="fa fa-spinner fa-spin me-1"></i>
                Loading notifications...
            </div>
        `;


        /*
        |--------------------------------------------------------------------------
        | Request Notification History
        |--------------------------------------------------------------------------
        */

        const response =
            await fetch(
                '/notifications',
                {
                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest',

                        'Accept':
                            'text/html',
                    },
                }
            );


        if (!response.ok) {

            throw new Error(
                'Failed to load notifications.'
            );
        }


        const html =
            await response.text();


        /*
        |--------------------------------------------------------------------------
        | Parse HTML
        |--------------------------------------------------------------------------
        */

        const parser =
            new DOMParser();

        const doc =
            parser.parseFromString(
                html,
                'text/html'
            );


        const tableBody =
            doc.querySelector(
                'tbody'
            );


        if (!tableBody) {

            list.innerHTML = `
                <div
                    class="px-3 py-3 text-danger small text-center"
                >
                    Failed to load notifications.
                </div>
            `;

            return;
        }


        const rows =
            tableBody.querySelectorAll(
                'tr'
            );


        /*
        |--------------------------------------------------------------------------
        | Clear Existing Dropdown
        |--------------------------------------------------------------------------
        */

        list.innerHTML = '';


        /*
        |--------------------------------------------------------------------------
        | No Notifications
        |--------------------------------------------------------------------------
        */

        if (rows.length === 0) {

            list.innerHTML = `
                <div
                    class="px-3 py-3 text-muted small text-center"
                    data-empty-notification
                >
                    <i class="fa fa-bell-slash me-1"></i>
                    No notifications.
                </div>
            `;

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | CSRF Token
        |--------------------------------------------------------------------------
        */

        const csrfToken =
            document.querySelector(
                'meta[name="csrf-token"]'
            )?.content;


        /*
        |--------------------------------------------------------------------------
        | Process Notification Rows
        |--------------------------------------------------------------------------
        */

        rows.forEach(row => {

            const cells =
                row.querySelectorAll(
                    'td'
                );


            /*
            |--------------------------------------------------------------------------
            | Your Table Has 7 Columns
            |--------------------------------------------------------------------------
            |
            | 0 = ID
            | 1 = Message
            | 2 = Type
            | 3 = Sender
            | 4 = Status
            | 5 = Received At
            | 6 = Actions
            |
            */

            if (cells.length < 7) {
                return;
            }


            const id =
                cells[0]
                    .textContent
                    .trim();


            const message =
                cells[1]
                    .querySelector('.fw-bold')
                    ?.textContent
                    .trim()
                ||
                cells[1]
                    .textContent
                    .trim();


            const type =
                cells[2]
                    .textContent
                    .trim();


            const sender =
                cells[3]
                    .textContent
                    .trim();


            const status =
                cells[4]
                    .textContent
                    .trim();


            const time =
                cells[5]
                    .textContent
                    .trim();


            /*
            |--------------------------------------------------------------------------
            | Check Read Status
            |--------------------------------------------------------------------------
            */

            const isUnread =
                status
                    .toLowerCase()
                    .includes(
                        'unread'
                    );


            /*
            |--------------------------------------------------------------------------
            | Create Dropdown Item
            |--------------------------------------------------------------------------
            */

            const item =
                document.createElement(
                    'div'
                );


            item.className =
                'dropdown-item-text small px-3 py-2 border-bottom';


            if (isUnread) {

                item.classList.add(
                    'bg-light'
                );
            }


            item.setAttribute(
                'data-notification-id',
                id
            );


            item.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">

                    <div class="me-2">

                        <div
                            class="${
                                isUnread
                                    ? 'fw-bold'
                                    : ''
                            }"
                        >

                            ${
                                isUnread
                                    ? `
                                        <i
                                            class="fa fa-circle text-primary me-1"
                                            style="font-size: 7px;"
                                        ></i>
                                    `
                                    : ''
                            }

                            ${escapeHtml(message)}

                        </div>

                        <div class="text-muted mt-1">

                            ${escapeHtml(type)}
                            •
                            ${escapeHtml(sender)}

                        </div>

                        <div class="text-muted">

                            ${escapeHtml(time)}

                        </div>

                    </div>

                    ${
                        isUnread
                            ? `
                                <span class="badge bg-warning text-dark">
                                    Unread
                                </span>
                            `
                            : `
                                <span class="badge bg-success">
                                    Read
                                </span>
                            `
                    }

                </div>

                ${
                    isUnread
                        ? `
                            <div class="mt-2">

                                <button
                                    type="button"
                                    class="btn btn-sm btn-success py-0 px-2 mark-notification-read"
                                    data-id="${id}"
                                >
                                    <i class="fa fa-check"></i>
                                    Mark Read
                                </button>

                            </div>
                        `
                        : ''
                }
            `;


            list.appendChild(
                item
            );
        });


        /*
        |--------------------------------------------------------------------------
        | Update Dropdown Count
        |--------------------------------------------------------------------------
        */

        const dropdownCount =
            document.getElementById(
                'notification-dropdown-count'
            );

        if (dropdownCount) {

            dropdownCount.textContent =
                `${unreadNotificationCount} Unread`;
        }


    } catch (error) {

        console.error(
            'Failed to load dropdown notifications:',
            error
        );

        list.innerHTML = `
            <div
                class="px-3 py-3 text-danger small text-center"
            >
                <i class="fa fa-exclamation-circle me-1"></i>
                Unable to load notifications.
            </div>
        `;
    }
}


/*
|--------------------------------------------------------------------------
| Show Toast
|--------------------------------------------------------------------------
*/

function showToast(
    message,
    type = 'success'
) {

    const container =
        document.getElementById(
            'toast-container'
        );

    if (!container) {
        return;
    }


    const toastId =
        'toast-' +
        Date.now();


    const toastEl =
        document.createElement(
            'div'
        );


    toastEl.id =
        toastId;


    toastEl.className =
        'toast align-items-center text-bg-' +
        (
            type === 'error'
                ? 'danger'
                : 'success'
        ) +
        ' border-0';


    toastEl.setAttribute(
        'role',
        'alert'
    );


    toastEl.setAttribute(
        'aria-live',
        'assertive'
    );


    toastEl.setAttribute(
        'aria-atomic',
        'true'
    );


    toastEl.innerHTML = `
        <div class="d-flex">

            <div class="toast-body">

                <i class="fa fa-bell me-1"></i>

                ${escapeHtml(message)}

            </div>

            <button
                type="button"
                class="btn-close btn-close-white me-2 m-auto"
                data-bs-dismiss="toast"
                aria-label="Close"
            ></button>

        </div>
    `;


    container.appendChild(
        toastEl
    );


    const toast =
        new bootstrap.Toast(
            toastEl,
            {
                delay: 4000
            }
        );


    toast.show();


    toastEl.addEventListener(
        'hidden.bs.toast',
        () => {
            toastEl.remove();
        }
    );
}


/*
|--------------------------------------------------------------------------
| Mark Notification As Read
|--------------------------------------------------------------------------
*/

async function markAsRead(id) {

    try {

        const csrfToken =
            document.querySelector(
                'meta[name="csrf-token"]'
            )?.content;


        const response =
            await fetch(
                `/notifications/mark-read/${id}`,
                {
                    method: 'POST',

                    headers: {
                        'X-CSRF-TOKEN':
                            csrfToken,

                        'X-Requested-With':
                            'XMLHttpRequest',

                        'Accept':
                            'application/json',

                        'Content-Type':
                            'application/json',
                    },
                }
            );


        if (!response.ok) {

            throw new Error(
                'Failed to mark notification as read.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Reload Actual Count From Server
        |--------------------------------------------------------------------------
        */

        await loadNotifications();


        /*
        |--------------------------------------------------------------------------
        | Reload Dropdown
        |--------------------------------------------------------------------------
        */

        await loadDropdownNotifications();


    } catch (error) {

        console.error(
            'Failed to mark notification as read:',
            error
        );
    }
}


/*
|--------------------------------------------------------------------------
| Realtime Pusher Notification
|--------------------------------------------------------------------------
*/

window.Echo
    .channel('notifications')
    .listen(
        '.received',
        (data) => {

            console.log(
                'Pusher event received:',
                data
            );


            const currentUserId =
                getCurrentUserId();


            const notification =
                data.notification;


            /*
            |--------------------------------------------------------------------------
            | Ignore Invalid Notifications
            |--------------------------------------------------------------------------
            */

            if (
                !currentUserId ||
                !notification
            ) {

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Ignore Other Users
            |--------------------------------------------------------------------------
            */

            if (
                String(
                    notification.user_id
                ) !==
                String(
                    currentUserId
                )
            ) {

                return;
            }


            console.log(
                'Realtime notification received:',
                notification
            );


            /*
            |--------------------------------------------------------------------------
            | Increase Badge
            |--------------------------------------------------------------------------
            */

            incrementBadge();


            /*
            |--------------------------------------------------------------------------
            | Add To Dropdown
            |--------------------------------------------------------------------------
            */

            addRealtimeNotificationToDropdown(
                notification
            );


            /*
            |--------------------------------------------------------------------------
            | Play Sound
            |--------------------------------------------------------------------------
            */

            playNotificationSound();


            /*
            |--------------------------------------------------------------------------
            | Show Toast
            |--------------------------------------------------------------------------
            */

            showToast(
                notification
                    .data
                    ?.message ||
                'New notification received'
            );


            /*
            |--------------------------------------------------------------------------
            | Update Dropdown Unread Count
            |--------------------------------------------------------------------------
            */

            const dropdownCount =
                document.getElementById(
                    'notification-dropdown-count'
                );

            if (dropdownCount) {

                dropdownCount.textContent =
                    `${unreadNotificationCount} Unread`;
            }
        }
    );


/*
|--------------------------------------------------------------------------
| DOM Ready
|--------------------------------------------------------------------------
|
| IMPORTANT:
| The navbar exists only after the DOM is loaded.
| Therefore notificationDropdown MUST be accessed here.
|
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    () => {

        /*
        |--------------------------------------------------------------------------
        | Initial Badge Count
        |--------------------------------------------------------------------------
        */

        loadNotifications();


        /*
        |--------------------------------------------------------------------------
        | Notification Dropdown
        |--------------------------------------------------------------------------
        */

        const notificationDropdown =
            document.getElementById(
                'notificationDropdown'
            );


        if (notificationDropdown) {

            notificationDropdown.addEventListener(
                'show.bs.dropdown',
                () => {

                    loadDropdownNotifications();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Mark Read Button
        |--------------------------------------------------------------------------
        |
        | Event delegation is used because notification
        | buttons are dynamically generated.
        |
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'click',
            (event) => {

                const button =
                    event.target.closest(
                        '.mark-notification-read'
                    );


                if (!button) {
                    return;
                }


                event.preventDefault();


                const id =
                    button.dataset.id;


                if (id) {

                    markAsRead(id);
                }

            }
        );

    }
);


/*
|--------------------------------------------------------------------------
| Export
|--------------------------------------------------------------------------
*/

export {};