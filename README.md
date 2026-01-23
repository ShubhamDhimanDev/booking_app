# MeetFlow

> **A modern, feature-rich appointment booking platform with calendar synchronization, flexible payment processing, and intelligent reminder system.**

[![Laravel](https://img.shields.io/badge/Laravel-9.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

MeetFlow is a powerful, scalable booking management system built with Laravel that enables professionals, consultants, coaches, and businesses to manage their appointments seamlessly. With Google Calendar integration, multi-gateway payment processing, and an intelligent refund system, MeetFlow simplifies the entire appointment lifecycle.

---

## 🎯 Problem Statement

**The Challenge:** Service providers struggle with fragmented booking tools that don't integrate with their existing workflows. Manual calendar management leads to double bookings, payment collection is scattered across multiple platforms, and refund handling is time-consuming and error-prone.

**The Solution:** MeetFlow consolidates booking management, calendar synchronization, payment processing, and automated communications into a single, cohesive platform. Organizers can focus on delivering value while MeetFlow handles the operational complexity.

---

## 🚀 Key Features

### ✅ **Currently Available**

#### 📅 **Smart Scheduling**
- **Google Calendar Integration:** Bidirectional sync with Google Calendar for real-time availability
- **Custom Timeslots:** Define specific time windows for different event types
- **Week Day Availability:** Set recurring availability patterns (e.g., only Mondays and Wednesdays)
- **Event Exclusions:** Block out specific dates or times (holidays, vacations, etc.)
- **Duration Management:** Flexible event durations from minutes to hours

#### 💳 **Flexible Payment Processing**
- **Multi-Gateway Support:** Razorpay and PayU integration with hot-swappable gateway architecture
- **Promo Code System:** Percentage and fixed discounts with usage limits and validity periods
- **Secure Credentials:** Encrypted payment gateway credentials storage
- **Payment Tracking:** Complete transaction history with status tracking
- **Admin Dashboard:** Centralized payment gateway configuration

#### 💰 **Intelligent Refund System**
- **Multiple Refund Policies:** Flexible, Moderate, Strict, and Custom policies
- **Time-Based Refunds:** Automatic refund percentage calculation based on cancellation timing
- **Gateway Charge Handling:** Optional gateway fee deduction from refunds
- **Refund Tracking:** Complete audit trail of all refund requests and processing
- **Automated Processing:** Queue-based refund job processing

#### 🔔 **Automated Reminders**
- **Event-Based Reminders:** Configure multiple reminders per event type
- **Flexible Timing:** Set reminder offsets (e.g., 24 hours, 2 hours, 30 minutes before)
- **Idempotency:** Prevents duplicate reminder sends with built-in tracking
- **Queue Processing:** Asynchronous reminder delivery for scalability
- **Email Notifications:** Professional email templates for all reminder types

#### 👥 **User Management**
- **Role-Based Access:** Admin and user roles with proper authorization
- **Organizer Accounts:** Service providers create and manage their events
- **Booker Profiles:** Users can view booking history and manage appointments
- **Google OAuth:** Seamless authentication via Google accounts

#### 📊 **Booking Management**
- **Status Tracking:** Pending, Confirmed, Declined, Rescheduled statuses
- **Soft Deletes:** Maintain booking history for audit and recovery
- **Cancellation System:** User-initiated cancellations with reason tracking
- **Rescheduling:** Allow bookers to reschedule within policy constraints
- **Phone Collection:** Optional phone number capture for better communication

#### 🎨 **User Experience**
- **Responsive Design:** TailwindCSS-based responsive interface
- **Dark Mode:** User preference for dark/light themes
- **InertiaJS + React:** Modern SPA experience with server-side rendering benefits
- **Public Booking Pages:** Shareable event links with clean, professional design

---

### 🚧 **Work In Progress (Coming Soon)**

#### 🌐 **Enhanced Integrations**
- [ ] Microsoft Outlook/Office 365 calendar sync
- [ ] Zoom/Google Meet automatic meeting link generation
- [ ] Slack/Discord notifications for organizers
- [ ] WhatsApp reminders via Twilio integration

#### 📈 **Analytics & Reporting**
- [ ] Revenue analytics dashboard
- [ ] Booking trends and patterns
- [ ] Promo code performance tracking
- [ ] Refund rate analysis
- [ ] Exportable reports (PDF, CSV)

#### 🔧 **Advanced Features**
- [ ] Group bookings (multiple attendees per slot)
- [ ] Recurring bookings (weekly, monthly subscriptions)
- [ ] Waitlist management for fully booked events
- [ ] Custom booking forms with additional fields
- [ ] Multi-language support (i18n)
- [ ] Time zone handling for international bookings

#### 💼 **Business Features**
- [ ] Team/organization management
- [ ] Resource sharing (conference rooms, equipment)
- [ ] Advanced permission system
- [ ] White-label options for agencies
- [ ] API for third-party integrations

---

## 🎓 Target Audience

### **Primary Users:**
- 💼 **Consultants & Coaches:** One-on-one sessions with clients
- 👨‍⚕️ **Healthcare Professionals:** Patient appointment management
- 🎤 **Speakers & Trainers:** Workshop and seminar bookings
- 💅 **Service Providers:** Salons, spas, personal services
- 🎓 **Educators & Tutors:** Private lessons and office hours
- 🏢 **Small Businesses:** Customer-facing appointment scheduling

### **Use Cases:**
- Initial consultations with payment collection
- Paid workshops with capacity limits
- Office hours with automatic calendar blocking
- Service appointments with refund protection
- Interview scheduling with reminder automation

---

## 🛠️ Technical Architecture

### **Backend Stack**
- **Framework:** Laravel 9.x
- **PHP Version:** 8.0+
- **Database:** MySQL/PostgreSQL
- **Queue System:** Laravel Queues (Database/Redis)
- **Authentication:** Laravel Breeze + Google OAuth
- **Authorization:** Spatie Laravel Permission

### **Frontend Stack**
- **Framework:** InertiaJS + React
- **Styling:** TailwindCSS 3.x
- **Build Tool:** Vite
- **State Management:** React Hooks

### **Key Integrations**
- **Google Calendar API:** OAuth2 calendar sync
- **Razorpay API:** Payment processing and refunds
- **PayU API:** Alternative payment gateway
- **Laravel Notifications:** Email and database notifications
- **Laravel Queues:** Asynchronous job processing

### **Design Patterns**
- **Service Layer:** Payment gateway abstraction (`PaymentGatewayInterface`)
- **Repository Pattern:** Centralized data access
- **Observer Pattern:** Booking lifecycle events
- **Queue Jobs:** Reminder processing, refund handling
- **Policy-Based Authorization:** Booking and Event policies

---

## 📦 Installation

### **Prerequisites**
- PHP 8.0 or higher
- Composer 2.x
- Node.js 16+ and NPM
- MySQL 5.7+ or PostgreSQL
- Google Cloud Project (for Calendar API)
- Razorpay/PayU account (for payments)

### **Setup Steps**

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd meetflow
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set up database**
   ```bash
   # Configure DB_* variables in .env
   php artisan migrate
   ```

5. **Configure services in `.env`**
   ```env
   # Google Calendar
   GOOGLE_CLIENT_ID=your_client_id
   GOOGLE_CLIENT_SECRET=your_client_secret
   GOOGLE_REDIRECT_URI=http://127.0.0.1:8000/auth/google/callback
   
   # Payment Gateways (or configure via admin panel)
   RAZORPAY_KEY=your_razorpay_key
   RAZORPAY_SECRET=your_razorpay_secret
   
   # Queue Configuration
   QUEUE_CONNECTION=database
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   # Or for development
   npm run dev
   ```

7. **Start the application**
   ```bash
   php artisan serve
   # Visit http://127.0.0.1:8000
   ```

8. **Run queue worker** (in separate terminal)
   ```bash
   php artisan queue:work
   ```

### **Important Notes**
- Google OAuth only works with `127.0.0.1`, not `.test` domains in local development
- Configure payment gateway credentials via Admin Panel → Payment Gateway Settings
- Set up cron job for scheduled tasks: `* * * * * php artisan schedule:run`

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=BookingTest

# Generate coverage report
php artisan test --coverage
```

---

## 📂 Project Structure

```
app/
├── Http/
│   ├── Controllers/       # Request handlers
│   ├── Middleware/        # IsAdmin, LinkedWithGoogle
│   └── Requests/          # Form validation
├── Models/                # Eloquent models
├── Services/              # PaymentGatewayManager, gateway services
├── Jobs/                  # BookingReminderJob, ProcessRefundJob
├── Notifications/         # Email notifications
└── Policies/              # Authorization policies

resources/
├── js/
│   └── Pages/            # InertiaJS React components
└── views/                # Blade templates

database/
├── migrations/           # Database schema
└── seeders/             # Sample data

routes/
├── web.php              # Public routes
├── auth.php             # Authentication routes
└── admin.php            # Admin panel routes
```

---

## 🔐 Security Features

- **Encrypted Settings:** Payment gateway credentials stored with Laravel encryption
- **CSRF Protection:** All forms protected against CSRF attacks
- **SQL Injection Prevention:** Eloquent ORM with parameterized queries
- **XSS Protection:** Input sanitization and output escaping
- **Role-Based Access:** Middleware and policy-based authorization
- **Secure Payment Processing:** PCI-compliant payment gateway integration
- **Soft Deletes:** Data retention for audit and recovery

---

## 🚀 Performance Optimizations

- **Database Indexing:** Strategic indexes on high-query columns
- **Eager Loading:** Prevents N+1 query problems
- **Queue Processing:** Async jobs for heavy operations
- **Laravel Caching:** Config, routes, and view caching
- **Asset Optimization:** Vite for optimized bundle sizes
- **Database Query Optimization:** Composite indexes for complex queries

See [optimizations.md](optimizations.md) for detailed optimization roadmap.

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 About the Developer

**MeetFlow** demonstrates proficiency in:
- **Full-Stack Development:** Laravel backend + React frontend integration
- **Complex Business Logic:** Multi-gateway payments, refund policies, time-based calculations
- **System Architecture:** Service layers, queue processing, event-driven design
- **Database Design:** Normalized schema with proper relationships and indexing
- **Third-Party Integrations:** Google Calendar API, payment gateways, OAuth
- **Security Best Practices:** Encryption, authorization, secure payment handling
- **Modern Development Practices:** InertiaJS, TailwindCSS, Vite, queue jobs
- **User Experience:** Responsive design, dark mode, intuitive interfaces

### **Technical Highlights:**
- Pluggable payment gateway architecture for easy provider switching
- Idempotent reminder system preventing duplicate notifications
- Time-based refund calculation engine with multiple policy types
- Soft delete implementation for data retention and recovery
- Composite database indexes for optimal query performance
- Queue-based job processing for scalability

---

## 📧 Contact & Support

For inquiries, suggestions, or collaboration opportunities:
- **Portfolio:** [Your Portfolio URL]
- **LinkedIn:** [Your LinkedIn]
- **Email:** [Your Email]
- **GitHub:** [Your GitHub Profile]

---

## 🙏 Acknowledgments

- Laravel Framework and Community
- Google Calendar API Documentation
- Razorpay and PayU Developer Resources
- TailwindCSS and InertiaJS Teams

---

**Built with ❤️ using Laravel, React, and modern web technologies**

*Last Updated: January 22, 2026*
