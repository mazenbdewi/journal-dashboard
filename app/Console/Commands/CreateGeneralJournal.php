<?php

namespace App\Console\Commands;

use App\Models\Journal;
use App\Models\JournalTranslation;
use App\Models\User;
use Illuminate\Console\Command;

class CreateGeneralJournal extends Command
{
    protected $signature = 'journal:create-general';

    protected $description = 'Create the General Journal with translations and assign it to the super admin user';

    public function handle(): int
    {
        $this->info('🔍 Looking for super admin...');

        $admin = User::role('super_admin')->first();

        if (! $admin) {
            $this->error('❌ No super admin found.');

            return self::FAILURE;
        }

        $this->info("✅ Found super admin: {$admin->name} ({$admin->email})");

        $journal = Journal::where('code', 'general')->first();

        if ($journal) {
            $this->warn('⚠️ General Journal already exists.');

            return self::SUCCESS;
        }

        $journal = Journal::create([
            'code' => 'general',
            'issn' => '0000-0000',
            'e_issn' => '0000-0000',
            'name' => 'General Journal',
            'created_by' => $admin->id,
        ]);

        // إنشاء الترجمة بالعربية
        JournalTranslation::create([
            'journal_id' => $journal->id,
            'locale' => 'ar',
            'title' => 'المجلة العامة',
            'slug' => 'المجلة-العامة',
            'description' => 'هذه المجلة العامة الافتراضية للنظام.',
        ]);

        // إنشاء الترجمة بالإنجليزية
        JournalTranslation::create([
            'journal_id' => $journal->id,
            'locale' => 'en',
            'title' => 'General Journal',
            'slug' => 'general-journal',
            'description' => 'This is the default general journal for the system.',
        ]);

        $this->info('📚 General Journal and its translations created successfully.');

        return self::SUCCESS;
    }
}
