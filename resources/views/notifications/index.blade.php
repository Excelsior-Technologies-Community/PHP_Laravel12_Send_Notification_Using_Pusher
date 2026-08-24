@extends('layouts.app')

@section('content')

<div class="container">

    <div class="row justify-content-center">

        <div class="col-md-12">

            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <span>
                        <i class="fa fa-bell"></i>
                        {{ __('Notification History') }}
                    </span>

                    <a
                        href="{{ route('notifications.send-form') }}"
                        class="btn btn-primary btn-sm"
                    >
                        <i class="fa fa-paper-plane"></i>
                        Send Notification
                    </a>

                </div>

                <div class="card-body">

                    @if(session('success'))

                        <div
                            class="alert alert-success alert-dismissible fade show"
                            role="alert"
                        >
                            <i class="fa fa-circle-check"></i>
                            {{ session('success') }}

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                            ></button>
                        </div>

                    @endif

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover">

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
                                        class="{{ is_null($notification->read_at) ? 'table-warning' : '' }}"
                                    >

                                        <td>
                                            {{ $notification->id }}
                                        </td>

                                        <td>

                                            <div class="fw-bold">

                                                {{ $notification->data['message'] ?? 'N/A' }}

                                            </div>

                                            @if(isset($notification->data['post_id']))

                                                <small class="text-muted">

                                                    Post ID:
                                                    {{ $notification->data['post_id'] }}

                                                </small>

                                            @endif

                                        </td>

                                        <td>

                                            <span class="badge bg-info text-dark">

                                                {{ ucfirst($notification->type) }}

                                            </span>

                                        </td>

                                        <td>

                                            {{ $notification->data['sender_name'] ?? 'System' }}

                                        </td>

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

                                        <td>

                                            {{ $notification->created_at->diffForHumans() }}

                                            <br>

                                            <small class="text-muted">

                                                {{ $notification->created_at->format('d M Y H:i:s') }}

                                            </small>

                                        </td>

                                        <td>

                                            @if(is_null($notification->read_at))

                                                <form
                                                    action="{{ route('notifications.mark-read', $notification->id) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                >

                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-success"
                                                    >
                                                        <i class="fa fa-check"></i>
                                                        Mark Read
                                                    </button>

                                                </form>

                                            @else

                                                <form
                                                    action="{{ route('notifications.mark-unread', $notification->id) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                >

                                                    @csrf

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-warning"
                                                    >
                                                        <i class="fa fa-envelope"></i>
                                                        Mark Unread
                                                    </button>

                                                </form>

                                            @endif

                                        </td>

                                    </tr>

                                @empty

                                    <tr>

                                        <td
                                            colspan="7"
                                            class="text-center py-4"
                                        >

                                            <i class="fa fa-bell-slash fa-2x text-muted"></i>

                                            <div class="mt-2 text-muted">

                                                No notifications found.

                                            </div>

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    <div class="mt-3">

                        {{ $notifications->links() }}

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection