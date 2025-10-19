<?php

namespace Database\Seeders;

use App\Models\Journal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeneralJournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Journal::where('code', 'general')->exists()) {
            return;
        }

        DB::transaction(function () {
            // إنشاء المجلة الأساسية
            $journal = Journal::create([
                'code' => 'general',
                'issn' => '0000-0000',
                'e_issn' => '0000-0000',
                'name' => 'General Journal',
                'slug' => 'general',
                'created_by' => 1, // افترض أن المستخدم الأول هو المسؤول
            ]);

            // إضافة الترجمات
            $journal->translations()->createMany([
                [
                    'locale' => 'en',
                    'title' => 'General Journal',
                    'description' => 'This is the general journal for all articles that do not belong to a specific journal.',
                    'keywords' => 'general, journal, articles',
                ],
                [
                    'locale' => 'ar',
                    'title' => 'مجلة عامة',
                    'description' => 'هذه هي المجلة العامة لجميع المقالات التي لا تنتمي إلى مجلة محددة.',
                    'keywords' => 'عام, مجلة, مقالات',
                ],
            ]);
        });

    }
}
