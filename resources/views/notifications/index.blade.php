@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fa fa-bell"></i> {{ __('Notification History') }}</span>
                    <a href="{{ route('notifications.send-form') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-paper-plane"></i> Send Notification
                    </a>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Message</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Received At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifications as $notification)
                                <tr class="{{ is_null($notification->read_at) ? 'table-warning' : '' }}">
                                    <td>{{ $notification->id }}</td>
                                    <td>{{ $notification->data['message'] ?? 'N/A' }}</td>
                                    <td>{{ ucfirst($notification->type) }}</td>
                                    <td>
                                        @if(is_null($notification->read_at))
                                            <span class="badge bg-warning text-dark">Unread</span>
                                        @else
                                            <span class="badge bg-success">Read</span>
                                        @endif
                                    </td>
                                    <td>{{ $notification->created_at->diffForHumans() }}</td>
                                    <td>
                                        @if(is_null($notification->read_at))
                                            <form action="{{ route('notifications.mark-read', $notification->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-sm btn-success">Mark Read</button>
                                            </form>
                                        @else
                                            <form action="{{ route('notifications.mark-unread', $notification->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-sm btn-warning">Mark Unread</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No notifications found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
