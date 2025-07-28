<?php

namespace App\Filament\Operator\Pages\Tenancy;

use App\Enums\OperatorStatus;
use App\Models\Operator;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Auth;

class OperatorStatusPage extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-information-circle';

    protected static ?string $title = 'Operator Status';

    protected static ?string $navigationLabel = 'Status';

    protected static ?int $navigationSort = 1;

    public Operator $operator;

    public function operatorInfolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->operator)
            ->columns(3)
            ->components([
                Grid::make()
                    ->columnSpan(2)
                    ->schema([
                        Section::make(fn (): string => $this->getStatusHeading())
                            ->columnSpan(2)
                            ->afterHeader(
                                TextEntry::make('status')
                                    ->key('operator-status')
                                    ->hiddenLabel()
                                    ->badge()
                                    ->color(fn (): string => $this->getStatusColor()),
                            )
                            ->schema([
                                TextEntry::make('description')
                                    ->hiddenLabel()
                                    ->state(fn (): string => $this->getStatusDescription())
                                    ->columnSpanFull(),
                            ])
                            ->columns(1),

                        Section::make('Operator Details')
                            ->columnSpan(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->weight(FontWeight::SemiBold),
                                TextEntry::make('type')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('contact_email')
                                    ->label('Contact Email'),
                                TextEntry::make('contact_phone')
                                    ->label('Contact Phone')
                                    ->placeholder('Not provided'),
                                TextEntry::make('created_at')
                                    ->label('Registration Date')
                                    ->date('M j, Y'),
                                TextEntry::make('updated_at')
                                    ->label('Last Updated')
                                    ->dateTime('M j, Y \a\t g:i A'),
                            ])
                            ->columns(2),

                    ]),

                Grid::make()
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Next Steps')
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('next_steps_display')
                                    ->hiddenLabel()
                                    ->state(fn (): array => $this->getNextSteps())
                                    ->bulleted()
                                    ->listWithLineBreaks()
                                    ->extraAttributes(['class' => 'text-gray-600 list-disc list-inside'])
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Need Help?')
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('support_text')
                                    ->hiddenLabel()
                                    ->state(fn (): string => 'If you have questions about your operator status or need assistance, please contact our support team at support@booked.com or visit our help center.')
                                    ->extraAttributes(['class' => 'text-gray-700'])
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public function getView(): string
    {
        return 'filament.operator.pages.tenancy.operator-status';
    }

    public function mount(): void
    {
        $this->operator = filament()->getTenant();

        abort_unless($this->operator, 404, 'Operator not found');
    }

    public function getStatusHeading(): string
    {
        return match ($this->operator->status) {
            OperatorStatus::PENDING => '⏳ Registration Under Review',
            OperatorStatus::APPROVED => '✅ Operator Approved',
            OperatorStatus::SUSPENDED => '⚠️ Account Suspended',
            OperatorStatus::REJECTED => '❌ Registration Rejected',
        };
    }

    public function getStatusDescription(): string
    {
        return match ($this->operator->status) {
            OperatorStatus::PENDING => 'Your operator registration is currently being reviewed by our team. We will notify you via email once a decision has been made.',
            OperatorStatus::APPROVED => 'Congratulations! Your operator account has been approved and is fully active. You now have access to all operator features.',
            OperatorStatus::SUSPENDED => 'Your operator account has been temporarily suspended. Please contact support for assistance in resolving this issue.',
            OperatorStatus::REJECTED => 'Unfortunately, your operator registration could not be approved at this time. Please review the feedback and consider reapplying.',
        };
    }

    /**
     * Get the status color.
     */
    public function getStatusColor(): string
    {
        return match ($this->operator->status) {
            OperatorStatus::PENDING => 'warning',
            OperatorStatus::APPROVED => 'success',
            OperatorStatus::SUSPENDED => 'danger',
            OperatorStatus::REJECTED => 'danger',
        };
    }

    /**
     * Get the next steps for the user.
     */
    public function getNextSteps(): array
    {
        return match ($this->operator->status) {
            OperatorStatus::PENDING => [
                'Check your email regularly for updates',
                'Ensure your contact information is up to date',
                'Be patient - review process typically takes 2-3 business days',
            ],
            OperatorStatus::APPROVED => [
                'Complete your operator profile',
                'Set up your booking settings',
                'Start managing your reservations',
                'Explore the operator dashboard features',
            ],
            OperatorStatus::SUSPENDED => [
                'Contact support immediately',
                'Review our terms of service',
                'Prepare any required documentation',
            ],
            OperatorStatus::REJECTED => [
                'Review the feedback provided',
                'Address any issues mentioned',
                'Consider submitting a new application',
                'Contact support if you need clarification',
            ],
        };
    }

    /**
     * Check if user can view this page.
     */
    public static function canAccess(): bool
    {
        $tenant = filament()->getTenant();
        $user = Auth::user();

        if (! $tenant instanceof Operator || ! $user) {
            return false;
        }

        return $user->canAccessTenant($tenant);
    }

    /**
     * Get the breadcrumbs for this page.
     */
    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Back',
            '#' => 'Operator Status',
        ];
    }
}
