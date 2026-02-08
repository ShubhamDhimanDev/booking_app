<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeder for subscription plans with multi-gateway support
     *
     * NOTE: Before running this seeder, create plans in your payment gateway:
     *
     * For Razorpay:
     * 1. Login to Razorpay Dashboard → Subscriptions → Plans
     * 2. Create plans and note down the plan IDs
     * 3. Update gateway_plan_ids below or in database after seeding
     *
     * For other gateways (Stripe, PayPal):
     * - Follow similar steps in their respective dashboards
     * - Add plan IDs to gateway_plan_ids array
     */
    public function run()
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for getting started with basic features',
                'features_list' => [
                    '10 Active Events',
                    '100 Bookings per month',
                    '3 Team Members',
                    '5 Promo Codes',
                    'Google Calendar Integration',
                    'Email Reminders',
                    'Basic Analytics',
                    'Email Support',
                ],
                'price_monthly' => 999.00,
                'price_yearly' => 9990.00, // ~17% discount
                'discount_yearly_percent' => 17,
                'gateway_plan_ids' => [
                    // Add your gateway plan IDs here after creating them
                    // 'razorpay' => [
                    //     'monthly' => 'plan_starter_monthly_id',
                    //     'yearly' => 'plan_starter_yearly_id',
                    // ],
                    // 'stripe' => [
                    //     'monthly' => 'price_starter_monthly_id',
                    //     'yearly' => 'price_starter_yearly_id',
                    // ],
                ],
                'max_events' => 10,
                'max_bookings_per_month' => 100,
                'max_team_members' => 3,
                'max_promo_codes' => 5,
                'custom_domain' => false,
                'white_label' => false,
                'api_access' => false,
                'priority_support' => false,
                'advanced_analytics' => false,
                'google_calendar' => true,
                'email_reminders' => true,
                'remove_branding' => false,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'For growing teams who need more power',
                'features_list' => [
                    '50 Active Events',
                    '500 Bookings per month',
                    '10 Team Members',
                    '20 Promo Codes',
                    'Google Calendar Integration',
                    'Email Reminders',
                    'Advanced Analytics',
                    'API Access',
                    'Priority Email Support',
                ],
                'price_monthly' => 2999.00,
                'price_yearly' => 29990.00, // ~17% discount
                'discount_yearly_percent' => 17,
                'gateway_plan_ids' => [
                    // Add your gateway plan IDs here after creating them
                ],
                'max_events' => 50,
                'max_bookings_per_month' => 500,
                'max_team_members' => 10,
                'max_promo_codes' => 20,
                'custom_domain' => false,
                'white_label' => false,
                'api_access' => true,
                'priority_support' => true,
                'advanced_analytics' => true,
                'google_calendar' => true,
                'email_reminders' => true,
                'remove_branding' => false,
                'is_active' => true,
                'is_featured' => true, // Recommended plan
                'sort_order' => 2,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'For established businesses with custom needs',
                'features_list' => [
                    '200 Active Events',
                    '2,000 Bookings per month',
                    '50 Team Members',
                    '50 Promo Codes',
                    'Custom Domain',
                    'White Label Branding',
                    'Google Calendar Integration',
                    'Email Reminders',
                    'Advanced Analytics',
                    'API Access',
                    'Priority Phone & Email Support',
                    'Remove "Powered by" Branding',
                ],
                'price_monthly' => 7999.00,
                'price_yearly' => 79990.00, // ~17% discount
                'discount_yearly_percent' => 17,
                'gateway_plan_ids' => [
                    // Add your gateway plan IDs here after creating them
                ],
                'max_events' => 200,
                'max_bookings_per_month' => 2000,
                'max_team_members' => 50,
                'max_promo_codes' => 50,
                'custom_domain' => true,
                'white_label' => true,
                'api_access' => true,
                'priority_support' => true,
                'advanced_analytics' => true,
                'google_calendar' => true,
                'email_reminders' => true,
                'remove_branding' => true,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Custom solutions for large organizations',
                'features_list' => [
                    'Unlimited Events',
                    'Unlimited Bookings',
                    'Unlimited Team Members',
                    'Unlimited Promo Codes',
                    'Custom Domain',
                    'White Label Branding',
                    'Google Calendar Integration',
                    'Email Reminders',
                    'Advanced Analytics',
                    'API Access',
                    'Dedicated Account Manager',
                    '24/7 Priority Support',
                    'Remove "Powered by" Branding',
                    'Custom Integrations',
                    'SLA Agreement',
                ],
                'price_monthly' => 19999.00,
                'price_yearly' => 199990.00, // ~17% discount
                'discount_yearly_percent' => 17,
                'gateway_plan_ids' => [
                    // Add your gateway plan IDs here after creating them
                ],
                'max_events' => 999999, // Unlimited
                'max_bookings_per_month' => 999999,
                'max_team_members' => 999999,
                'max_promo_codes' => 999999,
                'custom_domain' => true,
                'white_label' => true,
                'api_access' => true,
                'priority_support' => true,
                'advanced_analytics' => true,
                'google_calendar' => true,
                'email_reminders' => true,
                'remove_branding' => true,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::firstOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('✅ Subscription plans seeded successfully!');
        $this->command->info('');
        $this->command->warn('⚠️  IMPORTANT: You need to create these plans in Razorpay Dashboard:');
        $this->command->info('   1. Login to https://dashboard.razorpay.com/');
        $this->command->info('   2. Go to Subscriptions → Plans');
        $this->command->info('   3. Create plans with amounts:');
        $this->command->info('      - Starter Monthly: ₹999 (99900 paise)');
        $this->command->info('      - Starter Yearly: ₹9,990 (999000 paise)');
        $this->command->info('      - Growth Monthly: ₹2,999 (299900 paise)');
        $this->command->info('      - Growth Yearly: ₹29,990 (2999000 paise)');
        $this->command->info('      - Business Monthly: ₹7,999 (799900 paise)');
        $this->command->info('      - Business Yearly: ₹79,990 (7999000 paise)');
        $this->command->info('      - Enterprise Monthly: ₹19,999 (1999900 paise)');
        $this->command->info('      - Enterprise Yearly: ₹1,99,990 (19999000 paise)');
        $this->command->info('   4. Update the razorpay_plan_id_monthly and razorpay_plan_id_yearly in the database');
    }
}
