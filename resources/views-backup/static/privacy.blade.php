@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
    <div class="prose max-w-none dark:prose-invert">
        <h1>Privacy Policy</h1>

        <p>Last updated: {{ date('F j, Y') }}</p>

        <p>{{ config('app.name') }} (“we”, “us”, or “our”) collects and uses personal information to provide and improve the MeetFlow booking service. This Privacy Policy explains what information we collect, why we collect it, and how you can control it.</p>

        <h2>Information We Collect</h2>
        <ul>
            <li><strong>Account Information:</strong> Name, email, and profile information you provide when registering.</li>
            <li><strong>Booking Data:</strong> Event titles, times, attendee names and contact details needed to schedule and manage bookings.</li>
            <li><strong>Payment Data:</strong> Transaction records (we do not store full payment card details; payments are processed by third-party gateways).</li>
            <li><strong>Calendar Access:</strong> With your explicit Google OAuth consent we may access your Google Calendar to read and write events necessary for booking synchronization. Only the scopes required for calendar sync are requested. We do not access unrelated Google data.</li>
        </ul>

        <h2>How We Use Your Data</h2>
        <p>We use data to provide core functionality: create and manage bookings, synchronize calendars, send reminders, handle payments, and respond to support requests.</p>

        <h2>Third-Party Services</h2>
        <p>We use trusted third parties (payment gateways, Google APIs, email providers) to operate the service. Their use of your data is governed by their privacy policies.</p>

        <h2>Security</h2>
        <p>We implement reasonable technical and organizational measures to protect your data. However, no system can be guaranteed 100% secure.</p>

        <h2>Your Rights</h2>
        <p>You may review, update, or request deletion of your personal data by contacting us at the address below or via your account settings.</p>

        <h2>Contact</h2>
        <p>If you have questions about this policy or our data practices, contact: support at the email address configured for the app administrator.</p>
    </div>
@endsection
