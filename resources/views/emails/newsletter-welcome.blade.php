<!DOCTYPE html>
<html lang="en">
<body style="font-family:Arial,sans-serif;color:#17243a;line-height:1.6;">
    <h2>Welcome to College Street Online</h2>
    <p>Thank you for subscribing. You will now receive our latest book and store updates.</p>
    <p><a href="{{ route('newsletter.unsubscribe', $subscriber->unsubscribe_token) }}">Unsubscribe</a></p>
</body>
</html>
