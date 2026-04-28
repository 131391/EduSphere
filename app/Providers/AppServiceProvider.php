<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\Fee;
use App\Models\FeeType;
use App\Models\FeePayment;
use App\Models\Grade;
use App\Models\PaymentMethod;
use App\Models\Result;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\User;
use App\Policies\BookPolicy;
use App\Policies\ExamPolicy;
use App\Policies\ExamTypePolicy;
use App\Policies\FeePolicy;
use App\Policies\FeeTypePolicy;
use App\Policies\FeePaymentPolicy;
use App\Policies\GradePolicy;
use App\Policies\PaymentMethodPolicy;
use App\Policies\ResultPolicy;
use App\Policies\SchoolPolicy;
use App\Policies\StudentPolicy;
use App\Policies\StudentRegistrationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Force HTTPS in production (must be in register to affect asset URLs)
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Book::class, BookPolicy::class);
        Gate::policy(BookIssue::class, BookPolicy::class);
        Gate::policy(Exam::class, ExamPolicy::class);
        Gate::policy(ExamType::class, ExamTypePolicy::class);
        Gate::policy(Fee::class, FeePolicy::class);
        Gate::policy(FeeType::class, FeeTypePolicy::class);
        Gate::policy(FeePayment::class, FeePaymentPolicy::class);
        Gate::policy(Grade::class, GradePolicy::class);
        Gate::policy(PaymentMethod::class, PaymentMethodPolicy::class);
        Gate::policy(Result::class, ResultPolicy::class);
        Gate::policy(School::class, SchoolPolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(StudentRegistration::class, StudentRegistrationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        \App\Models\Waiver::observe(\App\Observers\WaiverObserver::class);

        // Set default string length for MySQL
        Schema::defaultStringLength(191);

        // Force HTTPS in production or when behind HTTPS proxy (Railway, Vercel, etc.)
        if (app()->environment('production') || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }
        
        // Prevent lazy loading in production
        if (app()->environment('production')) {
            Model::preventLazyLoading();
        }

        // Register fallback Livewire directives so missing Livewire packages don't render raw blade text.
        Blade::directive('livewireStyles', function () {
            if (class_exists(\Livewire\Livewire::class)) {
                return '<?php echo \\Livewire\\Livewire::styles(); ?>';
            }

            return '';
        });

        Blade::directive('livewireScripts', function () {
            if (class_exists(\Livewire\Livewire::class)) {
                return '<?php echo \\Livewire\\Livewire::scripts(); ?>';
            }

            return '';
        });
    }
}
