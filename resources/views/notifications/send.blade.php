@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><i class="fa fa-paper-plane"></i> {{ __('Send Notification') }}</div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('notifications.send') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label>Message:</label>
                            <textarea name="message" class="form-control" rows="4" placeholder="Enter notification message...">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label>Send To (Optional):</label>
                            <select name="user_id" class="form-control">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                        @if($user->is_admin)
                                            - Admin
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Leave blank to send to all users.</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-paper-plane"></i> Send Notification
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
