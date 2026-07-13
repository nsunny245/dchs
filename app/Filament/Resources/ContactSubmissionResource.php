<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactSubmissionResource\Pages;
use App\Models\ContactSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ContactSubmissionResource extends Resource
{
    protected static ?string $model = ContactSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';
    protected static ?string $navigationGroup = 'Student Relations';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Contact Submissions';
    protected static ?string $modelLabel = 'Contact Submission';
    protected static ?string $pluralModelLabel = 'Contact Submissions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Inquiry Submission Details')
                    ->description('View the message submitted from the contact form')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('subject')
                            ->disabled()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('message')
                            ->disabled()
                            ->columnSpanFull()
                            ->rows(4),
                    ])->columns(2)->columnSpanFull(),

                Forms\Components\Section::make('Response / Action')
                    ->description('Review or update the response status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending Review',
                                'read' => 'Read',
                                'responded' => 'Responded',
                            ])
                            ->required(),
                        Forms\Components\Textarea::make('response')
                            ->label('Saved Response Notes')
                            ->placeholder('No response recorded yet. Use the "Answer Submission" table action to send an email response.')
                            ->columnSpanFull()
                            ->rows(4),
                    ])->columns(2)->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('S.No')
                    ->rowIndex(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject')
                    ->placeholder('General Query')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'danger',
                        'read' => 'info',
                        'responded' => 'success',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
            ])
            ->actions([
                Tables\Actions\Action::make('respond')
                    ->label('Answer')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->form([
                        Forms\Components\Textarea::make('response_message')
                            ->label('Email Response')
                            ->required()
                            ->rows(6)
                            ->placeholder('Type your response to the contact inquiry here. This will be sent as an email to the sender.'),
                    ])
                    ->action(function (ContactSubmission $record, array $data) {
                        $record->update([
                            'status' => 'responded',
                            'response' => $data['response_message'],
                        ]);

                        // Send the email response to the submitter
                        try {
                            \Illuminate\Support\Facades\Mail::raw(
                                "Dear {$record->name},\n\n" .
                                "Thank you for contacting Daniyal Group of Colleges.\n\n" .
                                "We have received your query regarding: \"" . ($record->subject ?? 'General Query') . "\".\n\n" .
                                "Our administration has responded to your message:\n" .
                                "--------------------------------------------------\n" .
                                "{$data['response_message']}\n" .
                                "--------------------------------------------------\n\n" .
                                "Best regards,\n" .
                                "Administration Office\n" .
                                "Daniyal Group of Colleges\n" .
                                "https://daniyalgroupofcolleges.com",
                                function ($message) use ($record) {
                                    $message->to($record->email)
                                        ->subject("Response to your inquiry - Daniyal Group of Colleges");
                                }
                            );
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Failed sending response email: " . $e->getMessage());
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Response Sent')
                            ->success()
                            ->body('Your email response has been successfully sent to the sender.')
                            ->send();
                    })
                    ->visible(fn (ContactSubmission $record) => $record->status !== 'responded'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactSubmissions::route('/'),
            'create' => Pages\CreateContactSubmission::route('/create'),
            'edit' => Pages\EditContactSubmission::route('/{record}/edit'),
        ];
    }
}
