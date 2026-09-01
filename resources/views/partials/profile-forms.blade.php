<div class="a-grid a-grid-2" style="align-items:start;">
        <div class="a-card">
                <h3>Profile information</h3>
                <form method="POST" action="{{ $profileUpdateRoute }}">
                        @csrf 
                        @method('PUT')
                        <div class="a-form-group">
                                <label>Name</label>
                                <input name="name" class="a-input" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="a-form-group">
                                <label>Email address</label>
                                <input type="email" name="email" class="a-input" value="{{ old('email', $user->email) }}" required>
                        </div>
                        @if($user->isPublisher())
                                <div class="a-form-group">
                                        <label>Business name</label>
                                        <input name="business_name" class="a-input" value="{{ old('business_name', $user->publisher->business_name) }}" required>
                                </div>
                                <div class="a-form-group">
                                        <label>Contact details</label>
                                        <textarea name="contact_details" class="a-textarea">{{ old('contact_details', $user->publisher->contact_details) }}</textarea>
                                </div>
                        @endif
                        <button class="btn btn-primary">Save profile</button>
                </form>
        </div>
        <div class="a-card">
                <h3>Change password</h3>
                <form method="POST" action="{{ $passwordUpdateRoute }}">
                        @csrf
                        @method('PUT')
                        <div class="a-form-group">
                                <label>Current password</label>
                                <input type="password" name="current_password" class="a-input" required autocomplete="current-password">
                        </div>
                        <div class="a-form-group">
                                <label>New password</label>
                                <input type="password" name="password" class="a-input" required minlength="8" autocomplete="new-password"></div>
                        <div class="a-form-group">
                                <label>Confirm new password</label>
                                <input type="password" name="password_confirmation" class="a-input" required minlength="8" autocomplete="new-password">
                        </div>
                        <button class="btn btn-primary">Change password</button>
                </form>
        </div>
</div>