<div class="d-flex justify-content-center align-items-center vh-100">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-lg">
            <div class="card-header text-center bg-primary text-white">
                <h4>Reset Password Created only because i don't have frontend for the api</h4>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('password.update') }}" class="p-3">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <!-- Display Email Address as Text -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <p class="form-control-plaintext">{{ $email }}</p>
                        <input type="hidden" name="email" value="{{ $email }}">
                    </div>

                    <!-- New Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" required>
                        @error('password_confirmation')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
