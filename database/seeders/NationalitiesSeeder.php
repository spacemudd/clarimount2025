<?php

namespace Database\Seeders;

use App\Models\Nationality;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NationalitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nationalities = [
            // Middle East & Gulf Nationalities
            ['code' => 'SAU', 'name_en' => 'Saudi Arabian', 'name_ar' => 'سعودي'],
            ['code' => 'UAE', 'name_en' => 'Emirati', 'name_ar' => 'إماراتي'],
            ['code' => 'KWT', 'name_en' => 'Kuwaiti', 'name_ar' => 'كويتي'],
            ['code' => 'QAT', 'name_en' => 'Qatari', 'name_ar' => 'قطري'],
            ['code' => 'BHR', 'name_en' => 'Bahraini', 'name_ar' => 'بحريني'],
            ['code' => 'OMN', 'name_en' => 'Omani', 'name_ar' => 'عماني'],
            ['code' => 'JOR', 'name_en' => 'Jordanian', 'name_ar' => 'أردني'],
            ['code' => 'LBN', 'name_en' => 'Lebanese', 'name_ar' => 'لبناني'],
            ['code' => 'SYR', 'name_en' => 'Syrian', 'name_ar' => 'سوري'],
            ['code' => 'IRQ', 'name_en' => 'Iraqi', 'name_ar' => 'عراقي'],
            ['code' => 'IRN', 'name_en' => 'Iranian', 'name_ar' => 'إيراني'],
            ['code' => 'TUR', 'name_en' => 'Turkish', 'name_ar' => 'تركي'],
            ['code' => 'ISR', 'name_en' => 'Israeli', 'name_ar' => 'إسرائيلي'],
            ['code' => 'PSE', 'name_en' => 'Palestinian', 'name_ar' => 'فلسطيني'],
            ['code' => 'YEM', 'name_en' => 'Yemeni', 'name_ar' => 'يمني'],

            // North African Nationalities
            ['code' => 'EGY', 'name_en' => 'Egyptian', 'name_ar' => 'مصري'],
            ['code' => 'LBY', 'name_en' => 'Libyan', 'name_ar' => 'ليبي'],
            ['code' => 'TUN', 'name_en' => 'Tunisian', 'name_ar' => 'تونسي'],
            ['code' => 'DZA', 'name_en' => 'Algerian', 'name_ar' => 'جزائري'],
            ['code' => 'MAR', 'name_en' => 'Moroccan', 'name_ar' => 'مغربي'],
            ['code' => 'SDN', 'name_en' => 'Sudanese', 'name_ar' => 'سوداني'],

            // Major International Nationalities
            ['code' => 'USA', 'name_en' => 'American', 'name_ar' => 'أمريكي'],
            ['code' => 'GBR', 'name_en' => 'British', 'name_ar' => 'بريطاني'],
            ['code' => 'CAN', 'name_en' => 'Canadian', 'name_ar' => 'كندي'],
            ['code' => 'AUS', 'name_en' => 'Australian', 'name_ar' => 'أسترالي'],
            ['code' => 'NZL', 'name_en' => 'New Zealand', 'name_ar' => 'نيوزيلندي'],
            ['code' => 'DEU', 'name_en' => 'German', 'name_ar' => 'ألماني'],
            ['code' => 'FRA', 'name_en' => 'French', 'name_ar' => 'فرنسي'],
            ['code' => 'ITA', 'name_en' => 'Italian', 'name_ar' => 'إيطالي'],
            ['code' => 'ESP', 'name_en' => 'Spanish', 'name_ar' => 'إسباني'],
            ['code' => 'PRT', 'name_en' => 'Portuguese', 'name_ar' => 'برتغالي'],
            ['code' => 'NLD', 'name_en' => 'Dutch', 'name_ar' => 'هولندي'],
            ['code' => 'BEL', 'name_en' => 'Belgian', 'name_ar' => 'بلجيكي'],
            ['code' => 'CHE', 'name_en' => 'Swiss', 'name_ar' => 'سويسري'],
            ['code' => 'AUT', 'name_en' => 'Austrian', 'name_ar' => 'نمساوي'],
            ['code' => 'SWE', 'name_en' => 'Swedish', 'name_ar' => 'سويدي'],
            ['code' => 'NOR', 'name_en' => 'Norwegian', 'name_ar' => 'نرويجي'],
            ['code' => 'DNK', 'name_en' => 'Danish', 'name_ar' => 'دنماركي'],
            ['code' => 'FIN', 'name_en' => 'Finnish', 'name_ar' => 'فنلندي'],
            ['code' => 'RUS', 'name_en' => 'Russian', 'name_ar' => 'روسي'],
            ['code' => 'POL', 'name_en' => 'Polish', 'name_ar' => 'بولندي'],
            ['code' => 'GRC', 'name_en' => 'Greek', 'name_ar' => 'يوناني'],

            // Asian Nationalities
            ['code' => 'CHN', 'name_en' => 'Chinese', 'name_ar' => 'صيني'],
            ['code' => 'JPN', 'name_en' => 'Japanese', 'name_ar' => 'ياباني'],
            ['code' => 'KOR', 'name_en' => 'South Korean', 'name_ar' => 'كوري جنوبي'],
            ['code' => 'IND', 'name_en' => 'Indian', 'name_ar' => 'هندي'],
            ['code' => 'PAK', 'name_en' => 'Pakistani', 'name_ar' => 'باكستاني'],
            ['code' => 'BGD', 'name_en' => 'Bangladeshi', 'name_ar' => 'بنغلاديشي'],
            ['code' => 'LKA', 'name_en' => 'Sri Lankan', 'name_ar' => 'سريلانكي'],
            ['code' => 'THA', 'name_en' => 'Thai', 'name_ar' => 'تايلاندي'],
            ['code' => 'MYS', 'name_en' => 'Malaysian', 'name_ar' => 'ماليزي'],
            ['code' => 'SGP', 'name_en' => 'Singaporean', 'name_ar' => 'سنغافوري'],
            ['code' => 'IDN', 'name_en' => 'Indonesian', 'name_ar' => 'إندونيسي'],
            ['code' => 'PHL', 'name_en' => 'Filipino', 'name_ar' => 'فلبيني'],
            ['code' => 'VNM', 'name_en' => 'Vietnamese', 'name_ar' => 'فيتنامي'],
            ['code' => 'KAZ', 'name_en' => 'Kazakhstani', 'name_ar' => 'كازاخستاني'],
            ['code' => 'UZB', 'name_en' => 'Uzbekistani', 'name_ar' => 'أوزبكستاني'],
            ['code' => 'AFG', 'name_en' => 'Afghan', 'name_ar' => 'أفغاني'],

            // African Nationalities
            ['code' => 'ZAF', 'name_en' => 'South African', 'name_ar' => 'جنوب أفريقي'],
            ['code' => 'NGA', 'name_en' => 'Nigerian', 'name_ar' => 'نيجيري'],
            ['code' => 'KEN', 'name_en' => 'Kenyan', 'name_ar' => 'كيني'],
            ['code' => 'ETH', 'name_en' => 'Ethiopian', 'name_ar' => 'إثيوبي'],
            ['code' => 'GHA', 'name_en' => 'Ghanaian', 'name_ar' => 'غاني'],
            ['code' => 'UGA', 'name_en' => 'Ugandan', 'name_ar' => 'أوغندي'],
            ['code' => 'TZA', 'name_en' => 'Tanzanian', 'name_ar' => 'تنزاني'],
            ['code' => 'SOM', 'name_en' => 'Somali', 'name_ar' => 'صومالي'],
            ['code' => 'DJI', 'name_en' => 'Djiboutian', 'name_ar' => 'جيبوتي'],
            ['code' => 'ERI', 'name_en' => 'Eritrean', 'name_ar' => 'إريتري'],

            // South American Nationalities
            ['code' => 'BRA', 'name_en' => 'Brazilian', 'name_ar' => 'برازيلي'],
            ['code' => 'ARG', 'name_en' => 'Argentine', 'name_ar' => 'أرجنتيني'],
            ['code' => 'CHL', 'name_en' => 'Chilean', 'name_ar' => 'تشيلي'],
            ['code' => 'COL', 'name_en' => 'Colombian', 'name_ar' => 'كولومبي'],
            ['code' => 'PER', 'name_en' => 'Peruvian', 'name_ar' => 'بيروي'],
            ['code' => 'VEN', 'name_en' => 'Venezuelan', 'name_ar' => 'فنزويلي'],
            ['code' => 'URY', 'name_en' => 'Uruguayan', 'name_ar' => 'أوروغوياني'],
            ['code' => 'PRY', 'name_en' => 'Paraguayan', 'name_ar' => 'باراغوياني'],
            ['code' => 'BOL', 'name_en' => 'Bolivian', 'name_ar' => 'بوليفي'],
            ['code' => 'ECU', 'name_en' => 'Ecuadorian', 'name_ar' => 'إكوادوري'],

            // Additional Nationalities
            ['code' => 'MEX', 'name_en' => 'Mexican', 'name_ar' => 'مكسيكي'],
            ['code' => 'CUB', 'name_en' => 'Cuban', 'name_ar' => 'كوبي'],
            ['code' => 'JAM', 'name_en' => 'Jamaican', 'name_ar' => 'جامايكي'],
        ];

        foreach ($nationalities as $nationality) {
            Nationality::create($nationality);
        }
    }
}
