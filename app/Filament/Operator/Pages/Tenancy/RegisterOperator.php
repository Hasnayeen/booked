<?php

declare(strict_types=1);

namespace App\Filament\Operator\Pages\Tenancy;

use App\Enums\OperatorStatus;
use App\Enums\OperatorType;
use App\Models\Operator;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewOperatorRegistration;
use App\Notifications\OperatorRegistrationConfirmation;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class RegisterOperator extends RegisterTenant
{
    /**
     * Get the label for this registration page.
     */
    public static function getLabel(): string
    {
        return 'Register Operator';
    }

    /**
     * Get the navigation label for this page.
     */
    public static function getNavigationLabel(): string
    {
        return 'Register New Operator';
    }

    /**
     * Define the registration form.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Operator Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Enter your company or organization name')
                    ->helperText('This will be the name displayed throughout the system'),

                Select::make('type')
                    ->label('Operator Type')
                    ->required()
                    ->options([
                        OperatorType::Hotel->value => 'Hotel Operator',
                        OperatorType::Bus->value => 'Bus Operator',
                    ])
                    ->native(false)
                    ->placeholder('Select your business type')
                    ->helperText('Choose the type of service you provide'),

                TextInput::make('contact_email')
                    ->label('Contact Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->placeholder('contact@example.com')
                    ->helperText('Main contact email for your operator account')
                    ->unique(Operator::class, 'contact_email'),

                TextInput::make('contact_phone')
                    ->label('Contact Phone')
                    ->tel()
                    ->nullable()
                    ->maxLength(20)
                    ->placeholder('+1 (555) 123-4567')
                    ->helperText('Phone number for customer inquiries'),

                FileUpload::make('logo')
                    ->label('Logo')
                    ->nullable()
                    ->image()
                    ->disk('public')
                    ->directory('logo')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/svg+xml'])
                    ->imagePreviewHeight('150')
                    ->helperText('Upload your company logo (SVG, max 2MB)'),

                Textarea::make('description')
                    ->label('Description')
                    ->nullable()
                    ->maxLength(1000)
                    ->rows(4)
                    ->placeholder('Describe your business, services, and what makes you unique...')
                    ->helperText('Brief description of your operator services (optional)'),
            ]);
    }

    /**
     * Handle the registration process.
     */
    protected function handleRegistration(array $data): Operator
    {
        $logoFilename = null;
        if (isset($data['logo']) && $data['logo']) {
            $logoPath = $data['logo'];
            $logoFilename = pathinfo((string) $logoPath, PATHINFO_FILENAME);
        }

        // Create the operator with pending status
        $operator = Operator::create([
            'name' => $data['name'],
            'type' => OperatorType::from($data['type']),
            'status' => OperatorStatus::Pending, // Always start as pending
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'] ?? null,
            'logo' => $logoFilename,
            'description' => $data['description'] ?? null,
            'metadata' => [
                'registered_at' => now()->toISOString(),
                'registered_by' => Auth::id(),
                'registration_ip' => request()->ip(),
            ],
        ]);

        // Attach the current user as the admin of this operator
        $operatorAdminRole = Role::where('name', 'Operator Admin')->first();
        $operator->users()->attach(Auth::user(), [
            'role_id' => $operatorAdminRole->id,
            'joined_at' => now(),
        ]);

        // Send registration confirmation email
        Auth::user()->notify(new OperatorRegistrationConfirmation($operator));

        // Notify admins with approve_operator permission
        $admins = User::whereHas('roles.permissions', function ($query): void {
            $query->where('name', 'approve_operator');
        })->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewOperatorRegistration($operator, Auth::user()));
        }

        return $operator;
    }
}
