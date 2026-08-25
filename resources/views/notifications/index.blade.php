@extends('layouts.app')

@section('content')

<div class="container">

    {{-- ========================================================= --}}
    {{-- PAGE HEADER --}}
    {{-- ========================================================= --}}

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">

                <i class="fa fa-bell text-primary"></i>

                Notification History

            </h3>

            <p class="text-muted mb-0">

                Manage your realtime notifications

            </p>

        </div>

        <div class="d-flex gap-2 mt-2 mt-md-0">

            <a
                href="{{ route('notifications.send-form') }}"
                class="btn btn-primary">

                <i class="fa fa-paper-plane"></i>

                Send Notification

            </a>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- SUCCESS MESSAGE --}}
    {{-- ========================================================= --}}

    @if(session('success'))

    <div
        class="alert alert-success alert-dismissible fade show"
        role="alert">

        <i class="fa fa-circle-check"></i>

        {{ session('success') }}

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

    @endif


    {{-- ========================================================= --}}
    {{-- STATISTICS --}}
    {{-- ========================================================= --}}

    <div class="row g-3 mb-4">

        {{-- Total --}}
        <div class="col-md-4">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <p class="text-muted mb-1">
                                Total Notifications
                            </p>

                            <h3 class="fw-bold mb-0">
                                {{ $totalNotifications }}
                            </h3>

                        </div>

                        <div class="text-primary fs-2">

                            <i class="fa fa-bell"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Unread --}}
        <div class="col-md-4">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <p class="text-muted mb-1">
                                Unread
                            </p>

                            <h3 class="fw-bold mb-0 text-warning">
                                {{ $unreadNotifications }}
                            </h3>

                        </div>

                        <div class="text-warning fs-2">

                            <i class="fa fa-envelope"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- Read --}}
        <div class="col-md-4">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <p class="text-muted mb-1">
                                Read
                            </p>

                            <h3 class="fw-bold mb-0 text-success">
                                {{ $readNotifications }}
                            </h3>

                        </div>

                        <div class="text-success fs-2">

                            <i class="fa fa-envelope-open"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- SEARCH + FILTER --}}
    {{-- ========================================================= --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('notifications.index') }}">

                <div class="row g-3 align-items-end">

                    {{-- Search --}}
                    <div class="col-md-5">

                        <label class="form-label fw-semibold">
                            Search
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="fa fa-search"></i>

                            </span>

                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search message, sender or type..."
                                value="{{ request('search') }}">

                        </div>

                    </div>


                    {{-- Type --}}
                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Type
                        </label>

                        <select
                            name="type"
                            class="form-select">

                            <option value="">
                                All Types
                            </option>

                            @foreach($notificationTypes as $type)

                            <option
                                value="{{ $type }}"
                                {{ request('type') === $type ? 'selected' : '' }}>

                                {{ ucfirst($type) }}

                            </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Status --}}
                    <div class="col-md-2">

                        <label class="form-label fw-semibold">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select">

                            <option value="">
                                All
                            </option>

                            <option
                                value="unread"
                                {{ request('status') === 'unread' ? 'selected' : '' }}>

                                Unread

                            </option>

                            <option
                                value="read"
                                {{ request('status') === 'read' ? 'selected' : '' }}>

                                Read

                            </option>

                        </select>

                    </div>


                    {{-- Buttons --}}
                    <div class="col-md-2">

                        <div class="d-grid gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary">

                                <i class="fa fa-filter"></i>

                                Filter

                            </button>


                            <a
                                href="{{ route('notifications.index') }}"
                                class="btn btn-outline-secondary">

                                <i class="fa fa-refresh"></i>

                                Reset

                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- BULK ACTIONS --}}
    {{-- ========================================================= --}}

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">

        <div class="text-muted">

            Showing

            <strong>
                {{ $notifications->firstItem() ?? 0 }}
            </strong>

            -

            <strong>
                {{ $notifications->lastItem() ?? 0 }}
            </strong>

            of

            <strong>
                {{ $notifications->total() }}
            </strong>

        </div>


        <div class="d-flex gap-2">

            {{-- Mark All Read --}}
            @if($unreadNotifications > 0)

            <form
                method="POST"
                action="{{ route('notifications.mark-all-read') }}">

                @csrf

                <button
                    type="submit"
                    class="btn btn-success btn-sm">

                    <i class="fa fa-check-double"></i>

                    Mark All Read

                </button>

            </form>

            @endif


            {{-- Delete All --}}
            @if($totalNotifications > 0)

            <form
                method="POST"
                action="{{ route('notifications.destroy-all') }}"
                onsubmit="return confirm('Are you sure you want to delete all notifications?');">

                @csrf

                @method('DELETE')

                <button
                    type="submit"
                    class="btn btn-danger btn-sm">

                    <i class="fa fa-trash"></i>

                    Delete All

                </button>

            </form>

            @endif

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- NOTIFICATION TABLE --}}
    {{-- ========================================================= --}}

    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>ID</th>

                            <th>Message</th>

                            <th>Type</th>

                            <th>Sender</th>

                            <th>Status</th>

                            <th>Received At</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($notifications as $notification)

                        <tr
                            class="{{ is_null($notification->read_at) ? 'table-warning' : '' }}">

                            {{-- ID --}}
                            <td>
                                <strong>
                                    #{{ $loop->iteration + ($notifications->currentPage() - 1) * $notifications->perPage() }}
                                </strong>
                            </td>


                            {{-- Message --}}
                            <td>

                                <div class="fw-bold">

                                    {{ $notification->data['message'] ?? 'N/A' }}

                                </div>


                                @if(isset($notification->data['post_id']))

                                <small class="text-muted">

                                    <i class="fa fa-file"></i>

                                    Post ID:

                                    {{ $notification->data['post_id'] }}

                                </small>

                                @endif

                            </td>


                            {{-- Type --}}
                            <td>

                                <span class="badge bg-info text-dark">

                                    {{ ucfirst($notification->type) }}

                                </span>

                            </td>


                            {{-- Sender --}}
                            <td>

                                <i class="fa fa-user text-muted"></i>

                                {{ $notification->data['sender_name'] ?? 'System' }}

                            </td>


                            {{-- Status --}}
                            <td>

                                @if(is_null($notification->read_at))

                                <span class="badge bg-warning text-dark">

                                    <i class="fa fa-envelope"></i>

                                    Unread

                                </span>

                                @else

                                <span class="badge bg-success">

                                    <i class="fa fa-envelope-open"></i>

                                    Read

                                </span>

                                @endif

                            </td>


                            {{-- Time --}}
                            <td>

                                {{ $notification->created_at->diffForHumans() }}

                                <br>

                                <small class="text-muted">

                                    {{ $notification->created_at->format('d M Y H:i:s') }}

                                </small>

                            </td>


                            {{-- Actions --}}
                            <td>

                                <div class="d-flex flex-wrap gap-1">

                                    {{-- Mark Read / Unread --}}

                                    @if(is_null($notification->read_at))

                                    <form
                                        action="{{ route('notifications.mark-read', $notification->id) }}"
                                        method="POST">

                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-success"
                                            title="Mark as read">

                                            <i class="fa fa-check"></i>

                                        </button>

                                    </form>

                                    @else

                                    <form
                                        action="{{ route('notifications.mark-unread', $notification->id) }}"
                                        method="POST">

                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-warning"
                                            title="Mark as unread">

                                            <i class="fa fa-envelope"></i>

                                        </button>

                                    </form>

                                    @endif


                                    {{-- Delete --}}

                                    <form
                                        action="{{ route('notifications.destroy', $notification->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete this notification?');">

                                        @csrf

                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger"
                                            title="Delete notification">

                                            <i class="fa fa-trash"></i>

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>


                        @empty

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5">

                                <i
                                    class="fa fa-bell-slash fa-3x text-muted">
                                </i>

                                <div class="mt-3 text-muted">

                                    No notifications found.

                                </div>


                                @if(request()->hasAny(['search', 'type', 'status']))

                                <a
                                    href="{{ route('notifications.index') }}"
                                    class="btn btn-sm btn-outline-primary mt-2">

                                    Clear Filters

                                </a>

                                @endif

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- ========================================================= --}}
        {{-- NUMBER ONLY PAGINATION --}}
        {{-- ========================================================= --}}

        @if($notifications->hasPages())

        <div class="card-footer bg-white">

            <div class="d-flex justify-content-center">

                <nav aria-label="Notification pagination">

                    <ul class="pagination mb-0">

                        @for($page = 1; $page <= $notifications->lastPage(); $page++)

                            <li
                                class="page-item {{ $notifications->currentPage() == $page ? 'active' : '' }}">

                                <a
                                    class="page-link"
                                    href="{{ $notifications->appends(request()->query())->url($page) }}">

                                    {{ $page }}

                                </a>

                            </li>

                            @endfor

                    </ul>

                </nav>

            </div>

        </div>

        @endif

    </div>

</div>

@endsection