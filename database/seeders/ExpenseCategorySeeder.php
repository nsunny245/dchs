<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExpenseCategory;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Salaries & Wages', 'description' => 'Staff and faculty salaries, stipends, and allowances'],
            ['name' => 'Utilities & Bills', 'description' => 'Electricity, gas, water, internet, and telephone bills'],
            ['name' => 'Building Rent & Maintenance', 'description' => 'Campus building rental charges, repair, and ongoing maintenance'],
            ['name' => 'Office & Academic Supplies', 'description' => 'Stationery, printing, exam papers, lab supplies, and consumables'],
            ['name' => 'Marketing & Advertising', 'description' => 'Prospectus printing, social media ads, banners, and promotional events'],
            ['name' => 'Miscellaneous & Events', 'description' => 'College functions, sports days, emergency expenses, and hospitality'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
