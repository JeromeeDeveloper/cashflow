<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashflow Planning System</title>
    <link rel="stylesheet" href="{{ asset('auth/style.css') }}">
    <style>
        /* Lightweight modal styles scoped to login page */
        #privacyModal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.45);
            z-index: 9999;
            padding: 16px;
        }
        #privacyModal[aria-hidden="false"] { display: flex; }
        #privacyModal .modal-content {
            background: #ffffff;
            color: #2d3748;
            width: 100%;
            max-width: 520px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        #privacyModal .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #edf2f7;
        }
        #privacyModal .modal-title { font-size: 16px; font-weight: 600; }
        #privacyModal .modal-body { padding: 16px 20px; line-height: 1.55; font-size: 14px; }
        #privacyModal .modal-footer { padding: 14px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #edf2f7; }
        #privacyModal .btn-close {
            background: transparent;
            border: none;
            color: #4a5568;
            cursor: pointer;
            font-size: 18px;
        }
        #privacyModal .btn-primary {
            background: #4ECDC4;
            border: none;
            color: #ffffff;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
        }
        #privacyModal .btn-primary:focus { outline: 2px solid #b2f5ea; outline-offset: 2px; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="card-accent"></div>

            <div class="login-header">
                <div class="logo">
                    <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo">
                </div>
                <h1>Welcome User</h1>
                <p>Cashflow Planning System</p>
            </div>

            <form class="login-form" id="loginForm" method="POST" action="{{ url('/login') }}" novalidate>
                @csrf

                @if($errors->any())
                    <div class="alert alert-danger" style="background: #fed7d7; border: 1px solid #feb2b2; color: #c53030; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                        <strong>Login Error:</strong>
                        <ul style="margin: 8px 0 0 20px; padding: 0;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="form-field {{ $errors->has('email') ? 'error' : '' }}">
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                    <label for="email">Email Address</label>
                    <div class="field-line"></div>
                    <span class="error-message" id="emailError">
                        @if($errors->has('email'))
                            {{ $errors->first('email') }}
                        @endif
                    </span>
                </div>

                <div class="form-field {{ $errors->has('password') ? 'error' : '' }}">
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                    <label for="password">Password</label>
                    <button type="button" class="password-reveal" id="passwordToggle" aria-label="Toggle password visibility">
                        <svg class="eye-show" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M10 4C5.5 4 1.7 7.3 1 10c.7 2.7 4.5 6 9 6s8.3-3.3 9-6c-.7-2.7-4.5-6-9-6zm0 10a4 4 0 110-8 4 4 0 010 8zm0-6a2 2 0 100 4 2 2 0 000-4z" fill="currentColor"/>
                        </svg>
                        <svg class="eye-hide" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M3 3l14 14m-7-7a2 2 0 01-2-2m2 2a2 2 0 002 2m-2-2v.01M10 6a4 4 0 014 4m-4-4a4 4 0 00-4 4m4-4V4m0 10v2m4-6c.7-2.7-3.3-6-8-6m8 6c-.7 2.7-4.5 6-9 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div class="field-line"></div>
                    <span class="error-message" id="passwordError">
                        @if($errors->has('password'))
                            {{ $errors->first('password') }}
                        @endif
                    </span>
                </div>

                {{-- <div class="form-actions">
                    <label class="remember-checkbox">
                        <input type="checkbox" id="remember" name="remember">
                        <span class="checkbox-custom">
                            <svg width="12" height="10" viewBox="0 0 12 10" fill="none">
                                <path d="M1 5l3 3 7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="checkbox-label">Remember me</span>
                    </label>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div> --}}

                <button type="submit" class="signin-button">
                    <span class="button-text">Sign In</span>
                    <div class="button-loader">
                        <div class="loader-circle"></div>
                    </div>
                </button>
            </form>

            {{-- <div class="auth-divider">
                <span>or continue with</span>
            </div> --}}

            <div class="signup-prompt">
                <a href="#" class="signup-link">Privacy Policy</a>
            </div>

            <div class="success-state" id="successMessage">
                <div class="success-visual">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                        <circle cx="20" cy="20" r="20" fill="url(#successGradient)"/>
                        <path d="M12 20l6 6 10-10" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <defs>
                            <linearGradient id="successGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#4ECDC4"/>
                                <stop offset="100%" stop-color="#44A08D"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>

            </div>
        </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div id="privacyModal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="privacyTitle">
        <div class="modal-content" role="document">
            <div class="modal-header">
                <div id="privacyTitle" class="modal-title">Privacy Policy</div>
                <button type="button" class="btn-close" id="privacyClose" aria-label="Close">×</button>
            </div>
            <div class="modal-body">
                We value your privacy. This system only collects the information necessary to authenticate your account and provide access. By signing in, you agree to our responsible data handling practices.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-primary" id="privacyOk">Got it</button>
            </div>
        </div>
    </div>

    <script src="{{asset('auth/script.js')}}"></script>
</body>
</html>
