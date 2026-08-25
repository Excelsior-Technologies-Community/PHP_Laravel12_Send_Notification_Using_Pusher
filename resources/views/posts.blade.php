@extends('layouts.app')

@section('content')

<div class="container">

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>

            <h3 class="fw-bold mb-1">

                <i class="fa fa-file-lines text-primary"></i>

                Posts

            </h3>

            <p class="text-muted mb-0">

                Create and manage posts

            </p>

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
    {{-- REALTIME NOTIFICATION AREA --}}
    {{-- ========================================================= --}}

    <div id="notification"></div>


    {{-- ========================================================= --}}
    {{-- CREATE POST --}}
    {{-- ========================================================= --}}

    @if(auth()->check() && !auth()->user()->is_admin)

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-primary text-white">

            <i class="fa fa-plus-circle"></i>

            Create New Post

        </div>

        <div class="card-body">

            <form
                method="POST"
                action="{{ route('posts.store') }}">

                @csrf


                {{-- Title --}}
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Title
                    </label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        value="{{ old('title') }}"
                        placeholder="Enter post title">

                    @error('title')

                    <div class="text-danger mt-1">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


                {{-- Body --}}
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Body
                    </label>

                    <textarea
                        class="form-control"
                        name="body"
                        rows="5"
                        placeholder="Enter post content...">{{ old('body') }}</textarea>

                    @error('body')

                    <div class="text-danger mt-1">
                        {{ $message }}
                    </div>

                    @enderror

                </div>


                <button
                    type="submit"
                    class="btn btn-success">

                    <i class="fa fa-save"></i>

                    Create Post

                </button>

            </form>

        </div>

    </div>

    @endif


    {{-- ========================================================= --}}
    {{-- SEARCH --}}
    {{-- ========================================================= --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="{{ route('posts.index') }}">

                <div class="row g-3 align-items-end">

                    <div class="col-md-10">

                        <label class="form-label fw-semibold">

                            Search Posts

                        </label>

                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="fa fa-search"></i>

                            </span>

                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search by title, body or author..."
                                value="{{ request('search') }}">

                        </div>

                    </div>


                    <div class="col-md-2">

                        <div class="d-grid gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary">

                                <i class="fa fa-search"></i>

                                Search

                            </button>


                            @if(request('search'))

                            <a
                                href="{{ route('posts.index') }}"
                                class="btn btn-outline-secondary">

                                <i class="fa fa-refresh"></i>

                                Clear

                            </a>

                            @endif

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- POST LIST --}}
    {{-- ========================================================= --}}

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center">

                <strong>

                    <i class="fa fa-list"></i>

                    Post List

                </strong>

                <span class="text-muted">

                    {{ $posts->total() }}

                    {{ Str::plural('Post', $posts->total()) }}

                </span>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th width="80">
                                ID
                            </th>

                            <th>
                                Title
                            </th>

                            <th>
                                Body
                            </th>

                            <th>
                                Author
                            </th>

                            <th>
                                Created
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($posts as $post)

                        <tr>

                            <td>

                                <strong>
                                    #{{ $post->id }}
                                </strong>

                            </td>


                            <td>

                                <strong>
                                    {{ $post->title }}
                                </strong>

                            </td>


                            <td>

                                {{ Str::limit($post->body, 100) }}

                            </td>


                            <td>

                                <i class="fa fa-user text-muted"></i>

                                {{ $post->user->name ?? 'Unknown' }}

                            </td>


                            <td>

                                {{ $post->created_at->diffForHumans() }}

                                <br>

                                <small class="text-muted">

                                    {{ $post->created_at->format('d M Y H:i:s') }}

                                </small>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td
                                colspan="5"
                                class="text-center py-5">

                                <i
                                    class="fa fa-file-circle-xmark fa-3x text-muted">
                                </i>

                                <div class="mt-3 text-muted">

                                    @if(request('search'))

                                    No posts found for:

                                    <strong>
                                        "{{ request('search') }}"
                                    </strong>

                                    @else

                                    There are no posts.

                                    @endif

                                </div>


                                @if(request('search'))

                                <a
                                    href="{{ route('posts.index') }}"
                                    class="btn btn-sm btn-outline-primary mt-2">

                                    Clear Search

                                </a>

                                @endif

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- PAGINATION - NUMBERS ONLY --}}
        {{-- ===================================================== --}}

        @if($posts->hasPages())

        <div class="card-footer bg-white">

            <div class="d-flex justify-content-center">

                <nav aria-label="Posts pagination">

                    <ul class="pagination mb-0">

                        {{-- Previous --}}
                        @if($posts->onFirstPage())

                        <li class="page-item disabled">

                            <span class="page-link">
                                ‹
                            </span>

                        </li>

                        @else

                        <li class="page-item">

                            <a
                                class="page-link"
                                href="{{ $posts->previousPageUrl() }}"
                                rel="prev">

                                ‹

                            </a>

                        </li>

                        @endif


                        {{-- Page Numbers --}}
                        @foreach($posts->getUrlRange(1, $posts->lastPage()) as $page => $url)

                        @if($page == $posts->currentPage())

                        <li class="page-item active">

                            <span class="page-link">
                                {{ $page }}
                            </span>

                        </li>

                        @else

                        <li class="page-item">

                            <a
                                class="page-link"
                                href="{{ $url }}">

                                {{ $page }}

                            </a>

                        </li>

                        @endif

                        @endforeach


                        {{-- Next --}}
                        @if($posts->hasMorePages())

                        <li class="page-item">

                            <a
                                class="page-link"
                                href="{{ $posts->nextPageUrl() }}"
                                rel="next">

                                ›

                            </a>

                        </li>

                        @else

                        <li class="page-item disabled">

                            <span class="page-link">
                                ›
                            </span>

                        </li>

                        @endif

                    </ul>

                </nav>

            </div>

        </div>

        @endif

    </div>

</div>

@endsection