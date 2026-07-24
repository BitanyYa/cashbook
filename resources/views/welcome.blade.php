<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="CashBook is a free, secure cash management app to track your finances easily. No subscriptions or hidden fees." />

    <title>{{ config('app.name', 'CashBook') }} - Free Cash Management</title>
</head>
<body style="margin:0; font-family: Inter, Arial, sans-serif; background-color:#f9fafb; color:#1f2937;">

    <!-- Navbar -->
    <header style="background:white; box-shadow:0 1px 3px rgba(0,0,0,0.1);">
        <div style="max-width:1200px; margin:0 auto; padding:0 16px; height:64px; display:flex; align-items:center; justify-content:space-between;">
            <h1 style="font-size:20px; font-weight:700; color:#16a34a;">
                <img src="{{ Storage::url('images/logo.svg') }}" alt="CashBook Logo" style="height: 32px; width: auto; vertical-align: middle;">
                CashBook
            </h1>
            <nav style="display:flex; gap:16px; align-items:center;">
                @auth
                    <a href="{{ route('books.index') }}" style="color:#374151; text-decoration:none;">Cashbooks</a>
                @else
                    <a href="{{ route('login') }}" style="color:#374151; text-decoration:none;">Login</a>
                    <a href="{{ route('register') }}" style="padding:8px 16px; background:#16a34a; color:white; border-radius:8px; text-decoration:none;">Get Started</a>
                @endauth
            </nav>
        </div>
    </header>

    <!-- Hero -->
    <section style="background:linear-gradient(to bottom right,#f0fdf4,white); padding:64px 16px; text-align:center;">
        <div style="max-width:700px; margin:0 auto;">
            <h2 style="font-size:36px; font-weight:800; color:#111827; margin:0;">Simple, Powerful Cash Management</h2>
            <p style="margin-top:16px; font-size:18px; color:#4b5563;">
                Track your cash flow with ease. No subscriptions, no hidden fees —
                <span style="color:#16a34a; font-weight:600;">100% free</span> forever.
            </p>
            <div style="margin-top:32px; display:flex; gap:16px; flex-wrap:wrap; justify-content:center;">
                @auth
                    <a href="{{ route('dashboard') }}" style="padding:12px 24px; background:#16a34a; color:white; border-radius:8px; text-decoration:none;">Go to Dashboard</a>
                @else
                    <a href="{{ route('register') }}" style="padding:12px 24px; background:#16a34a; color:white; border-radius:8px; text-decoration:none;">Create Free Account</a>
                    <a href="{{ route('login') }}" style="padding:12px 24px; border:1px solid #d1d5db; border-radius:8px; text-decoration:none; color:#374151; background:white;">Sign In</a>
                    <a href="{{ route('login', ['demo' => true]) }}" style="padding:12px 24px; border:1px solid #d1d5db; border-radius:8px; text-decoration:none; color:#374151; background:white;">View Demo</a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Features -->
    <section style="padding:64px 16px;">
        <div style="max-width:1200px; margin:0 auto;">
            <h3 style="text-align:center; font-size:24px; font-weight:700; margin-bottom:48px;">Everything You Need to Manage Your Cash Flow</h3>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:24px;">
                @foreach([
                    ['📚', 'Multiple Cash Books', 'Organize finances for different projects or departments easily.'],
                    ['🏢', 'Multi-Business Support', 'Switch between businesses seamlessly from one account.'],
                    ['📊', 'Advanced Analytics', 'Get insights with real-time charts & reports.'],
                    ['🔒', 'Secure & Reliable', 'Enterprise-grade security and daily backups.'],
                    ['💳', 'Transaction Management', 'Categorize income & expenses with receipt uploads.'],
                    ['⚡', 'Lightning Fast', 'Quick data entry & instant search.'],
                    ['💻', 'Open Source Tech', 'Built with Laravel, Vue.js, Bootstrap, and MySQL.']
                ] as [$icon, $title, $desc])
                    <div style="background:white; border-radius:12px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                        <div style="font-size:28px; margin-bottom:16px;">{{ $icon }}</div>
                        <h4 style="font-size:18px; font-weight:600; margin-bottom:8px;">{{ $title }}</h4>
                        <p style="color:#4b5563; font-size:15px;">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section style="padding:64px 16px; text-align:center; background:#f3f4f6;">
        <h3 style="font-size:24px; font-weight:700; margin-bottom:16px;">
            Join the CashBook Community — 100% Free & Open Source
        </h3>
        <p style="color:#4b5563; font-size:16px; margin-bottom:32px;">
            Track your cash flow with a secure, user-friendly app trusted by thousands.
            <span style="color:#16a34a; font-weight:600;">No subscriptions, no hidden fees.</span>
        </p>
        @guest
            <a href="{{ route('register') }}" style="padding:12px 24px; background:#16a34a; color:white; border-radius:8px; text-decoration:none; margin-right:16px;">Create Free Account</a>
        @endguest
        <a href="https://github.com/Riaz-Mahmud/cashbook" target="_blank" rel="noopener noreferrer" style="padding:12px 24px; border:2px solid #16a34a; border-radius:8px; text-decoration:none; color:#16a34a; font-weight:600;">
            View on GitHub
        </a>
    </section>

    <!-- Stats -->
    <section style="background:#f3f4f6; padding:64px 16px;">
        <div style="max-width:1200px; margin:0 auto; display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:24px; text-align:center;">
            <div>
                <div style="font-size:28px; font-weight:700; color:#16a34a;">10,000+</div>
                <p style="color:#4b5563;">Active Users</p>
            </div>
            <div>
                <div style="font-size:28px; font-weight:700; color:#16a34a;">$50M+</div>
                <p style="color:#4b5563;">Tracked Revenue</p>
            </div>
            <div>
                <div style="font-size:28px; font-weight:700; color:#16a34a;">99.9%</div>
                <p style="color:#4b5563;">Uptime</p>
            </div>
            <div>
                <div style="font-size:28px; font-weight:700; color:#16a34a;">24/7</div>
                <p style="color:#4b5563;">Support</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section style="padding:64px 16px; text-align:center;">
        <h3 style="font-size:24px; font-weight:700; margin-bottom:16px;">Ready to Take Control of Your Cash Flow?</h3>
        <p style="color:#4b5563; font-size:16px; margin-bottom:32px;">
            Sign up now and start managing your finances smarter — <span style="color:#16a34a; font-weight:600;">100% free</span>.
        </p>
        @guest
            <a href="{{ route('register') }}" style="padding:12px 24px; background:#16a34a; color:white; border-radius:8px; text-decoration:none;">Create Free Account</a>
        @endguest
    </section>

    <!-- Footer -->
    <footer style="background:#1f2937; color:#9ca3af; padding:32px 16px; text-align:center;">
        <p style="margin-bottom:16px;">&copy; {{ date('Y') }} CashBook. All rights reserved.</p>
        <div style="display:flex; gap:24px; justify-content:center; flex-wrap:wrap; font-size:14px;">
            <a href="{{ route('privacy') }}" style="color:#9ca3af; text-decoration:none;">Privacy Policy</a>
            <a href="#" style="color:#9ca3af; text-decoration:none;">Terms</a>
            <a href="#" style="color:#9ca3af; text-decoration:none;">Support</a>
            <a href="#" style="color:#9ca3af; text-decoration:none;">Contact</a>
        </div>
    </footer>

</body>
</html>
