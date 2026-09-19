@extends('layouts/blankLayout')

@section('title', 'Login Basic - Pages')

@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
  <div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner">
        <!-- Register -->
        <div class="card px-sm-6 px-0">
          <div class="card-body">
            <!-- /Logo -->
            <h4 class="mb-1">Welcome to My Page!</h4>

            @if($errors->has('loginError'))
              <div class="alert alert-danger py-2 px-3 mb-3 text-xs">
                {{ $errors->first('loginError') }}
              </div>
            @endif

            @if(session('success'))
              <div class="alert alert-success py-2 px-3 mb-3 text-xs">
                {{ session('success') }}
              </div>
            @endif

            <form id="formAuthentication" class="mb-6" action="{{ route('login.post') }}" method="POST">
              @csrf
              <div class="mb-6">
                <label for="email" class="form-label">Username</label>
                <input type="text" class="form-control" id="email" name="email-username"
                  placeholder="Enter username" autofocus />
              </div>
              <div class="mb-6 form-password-toggle">
                <label class="form-label" for="password">Password</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password" class="form-control" name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    aria-describedby="password" />
                  <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                </div>
              </div>
              <div class="mb-6">
                <button class="btn btn-primary d-grid w-100" type="submit">Login</button>
              </div>
            </form>
          </div>
        </div>
        <!-- /Register -->
      </div>
    </div>
  </div>
@endsection
