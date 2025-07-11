<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            // Middle East & Gulf Countries
            ['code' => 'SA', 'code_alpha3' => 'SAU', 'name_en' => 'Saudi Arabia', 'name_ar' => 'المملكة العربية السعودية'],
            ['code' => 'AE', 'code_alpha3' => 'ARE', 'name_en' => 'United Arab Emirates', 'name_ar' => 'الإمارات العربية المتحدة'],
            ['code' => 'KW', 'code_alpha3' => 'KWT', 'name_en' => 'Kuwait', 'name_ar' => 'الكويت'],
            ['code' => 'QA', 'code_alpha3' => 'QAT', 'name_en' => 'Qatar', 'name_ar' => 'قطر'],
            ['code' => 'BH', 'code_alpha3' => 'BHR', 'name_en' => 'Bahrain', 'name_ar' => 'البحرين'],
            ['code' => 'OM', 'code_alpha3' => 'OMN', 'name_en' => 'Oman', 'name_ar' => 'عُمان'],
            ['code' => 'JO', 'code_alpha3' => 'JOR', 'name_en' => 'Jordan', 'name_ar' => 'الأردن'],
            ['code' => 'LB', 'code_alpha3' => 'LBN', 'name_en' => 'Lebanon', 'name_ar' => 'لبنان'],
            ['code' => 'SY', 'code_alpha3' => 'SYR', 'name_en' => 'Syria', 'name_ar' => 'سوريا'],
            ['code' => 'IQ', 'code_alpha3' => 'IRQ', 'name_en' => 'Iraq', 'name_ar' => 'العراق'],
            ['code' => 'IR', 'code_alpha3' => 'IRN', 'name_en' => 'Iran', 'name_ar' => 'إيران'],
            ['code' => 'TR', 'code_alpha3' => 'TUR', 'name_en' => 'Turkey', 'name_ar' => 'تركيا'],
            ['code' => 'IL', 'code_alpha3' => 'ISR', 'name_en' => 'Israel', 'name_ar' => 'إسرائيل'],
            ['code' => 'PS', 'code_alpha3' => 'PSE', 'name_en' => 'Palestine', 'name_ar' => 'فلسطين'],
            ['code' => 'YE', 'code_alpha3' => 'YEM', 'name_en' => 'Yemen', 'name_ar' => 'اليمن'],

            // North Africa
            ['code' => 'EG', 'code_alpha3' => 'EGY', 'name_en' => 'Egypt', 'name_ar' => 'مصر'],
            ['code' => 'LY', 'code_alpha3' => 'LBY', 'name_en' => 'Libya', 'name_ar' => 'ليبيا'],
            ['code' => 'TN', 'code_alpha3' => 'TUN', 'name_en' => 'Tunisia', 'name_ar' => 'تونس'],
            ['code' => 'DZ', 'code_alpha3' => 'DZA', 'name_en' => 'Algeria', 'name_ar' => 'الجزائر'],
            ['code' => 'MA', 'code_alpha3' => 'MAR', 'name_en' => 'Morocco', 'name_ar' => 'المغرب'],
            ['code' => 'SD', 'code_alpha3' => 'SDN', 'name_en' => 'Sudan', 'name_ar' => 'السودان'],

            // Major International Countries
            ['code' => 'US', 'code_alpha3' => 'USA', 'name_en' => 'United States', 'name_ar' => 'الولايات المتحدة'],
            ['code' => 'GB', 'code_alpha3' => 'GBR', 'name_en' => 'United Kingdom', 'name_ar' => 'المملكة المتحدة'],
            ['code' => 'CA', 'code_alpha3' => 'CAN', 'name_en' => 'Canada', 'name_ar' => 'كندا'],
            ['code' => 'AU', 'code_alpha3' => 'AUS', 'name_en' => 'Australia', 'name_ar' => 'أستراليا'],
            ['code' => 'NZ', 'code_alpha3' => 'NZL', 'name_en' => 'New Zealand', 'name_ar' => 'نيوزيلندا'],
            ['code' => 'DE', 'code_alpha3' => 'DEU', 'name_en' => 'Germany', 'name_ar' => 'ألمانيا'],
            ['code' => 'FR', 'code_alpha3' => 'FRA', 'name_en' => 'France', 'name_ar' => 'فرنسا'],
            ['code' => 'IT', 'code_alpha3' => 'ITA', 'name_en' => 'Italy', 'name_ar' => 'إيطاليا'],
            ['code' => 'ES', 'code_alpha3' => 'ESP', 'name_en' => 'Spain', 'name_ar' => 'إسبانيا'],
            ['code' => 'PT', 'code_alpha3' => 'PRT', 'name_en' => 'Portugal', 'name_ar' => 'البرتغال'],
            ['code' => 'NL', 'code_alpha3' => 'NLD', 'name_en' => 'Netherlands', 'name_ar' => 'هولندا'],
            ['code' => 'BE', 'code_alpha3' => 'BEL', 'name_en' => 'Belgium', 'name_ar' => 'بلجيكا'],
            ['code' => 'CH', 'code_alpha3' => 'CHE', 'name_en' => 'Switzerland', 'name_ar' => 'سويسرا'],
            ['code' => 'AT', 'code_alpha3' => 'AUT', 'name_en' => 'Austria', 'name_ar' => 'النمسا'],
            ['code' => 'SE', 'code_alpha3' => 'SWE', 'name_en' => 'Sweden', 'name_ar' => 'السويد'],
            ['code' => 'NO', 'code_alpha3' => 'NOR', 'name_en' => 'Norway', 'name_ar' => 'النرويج'],
            ['code' => 'DK', 'code_alpha3' => 'DNK', 'name_en' => 'Denmark', 'name_ar' => 'الدنمارك'],
            ['code' => 'FI', 'code_alpha3' => 'FIN', 'name_en' => 'Finland', 'name_ar' => 'فنلندا'],
            ['code' => 'RU', 'code_alpha3' => 'RUS', 'name_en' => 'Russia', 'name_ar' => 'روسيا'],
            ['code' => 'PL', 'code_alpha3' => 'POL', 'name_en' => 'Poland', 'name_ar' => 'بولندا'],
            ['code' => 'GR', 'code_alpha3' => 'GRC', 'name_en' => 'Greece', 'name_ar' => 'اليونان'],

            // Asian Countries
            ['code' => 'CN', 'code_alpha3' => 'CHN', 'name_en' => 'China', 'name_ar' => 'الصين'],
            ['code' => 'JP', 'code_alpha3' => 'JPN', 'name_en' => 'Japan', 'name_ar' => 'اليابان'],
            ['code' => 'KR', 'code_alpha3' => 'KOR', 'name_en' => 'South Korea', 'name_ar' => 'كوريا الجنوبية'],
            ['code' => 'IN', 'code_alpha3' => 'IND', 'name_en' => 'India', 'name_ar' => 'الهند'],
            ['code' => 'PK', 'code_alpha3' => 'PAK', 'name_en' => 'Pakistan', 'name_ar' => 'باكستان'],
            ['code' => 'BD', 'code_alpha3' => 'BGD', 'name_en' => 'Bangladesh', 'name_ar' => 'بنغلاديش'],
            ['code' => 'LK', 'code_alpha3' => 'LKA', 'name_en' => 'Sri Lanka', 'name_ar' => 'سريلانكا'],
            ['code' => 'TH', 'code_alpha3' => 'THA', 'name_en' => 'Thailand', 'name_ar' => 'تايلاند'],
            ['code' => 'MY', 'code_alpha3' => 'MYS', 'name_en' => 'Malaysia', 'name_ar' => 'ماليزيا'],
            ['code' => 'SG', 'code_alpha3' => 'SGP', 'name_en' => 'Singapore', 'name_ar' => 'سنغافورة'],
            ['code' => 'ID', 'code_alpha3' => 'IDN', 'name_en' => 'Indonesia', 'name_ar' => 'إندونيسيا'],
            ['code' => 'PH', 'code_alpha3' => 'PHL', 'name_en' => 'Philippines', 'name_ar' => 'الفلبين'],
            ['code' => 'VN', 'code_alpha3' => 'VNM', 'name_en' => 'Vietnam', 'name_ar' => 'فيتنام'],
            ['code' => 'KZ', 'code_alpha3' => 'KAZ', 'name_en' => 'Kazakhstan', 'name_ar' => 'كازاخستان'],
            ['code' => 'UZ', 'code_alpha3' => 'UZB', 'name_en' => 'Uzbekistan', 'name_ar' => 'أوزبكستان'],
            ['code' => 'AF', 'code_alpha3' => 'AFG', 'name_en' => 'Afghanistan', 'name_ar' => 'أفغانستان'],

            // African Countries
            ['code' => 'ZA', 'code_alpha3' => 'ZAF', 'name_en' => 'South Africa', 'name_ar' => 'جنوب أفريقيا'],
            ['code' => 'NG', 'code_alpha3' => 'NGA', 'name_en' => 'Nigeria', 'name_ar' => 'نيجيريا'],
            ['code' => 'KE', 'code_alpha3' => 'KEN', 'name_en' => 'Kenya', 'name_ar' => 'كينيا'],
            ['code' => 'ET', 'code_alpha3' => 'ETH', 'name_en' => 'Ethiopia', 'name_ar' => 'إثيوبيا'],
            ['code' => 'GH', 'code_alpha3' => 'GHA', 'name_en' => 'Ghana', 'name_ar' => 'غانا'],
            ['code' => 'UG', 'code_alpha3' => 'UGA', 'name_en' => 'Uganda', 'name_ar' => 'أوغندا'],
            ['code' => 'TZ', 'code_alpha3' => 'TZA', 'name_en' => 'Tanzania', 'name_ar' => 'تنزانيا'],
            ['code' => 'SO', 'code_alpha3' => 'SOM', 'name_en' => 'Somalia', 'name_ar' => 'الصومال'],
            ['code' => 'DJ', 'code_alpha3' => 'DJI', 'name_en' => 'Djibouti', 'name_ar' => 'جيبوتي'],
            ['code' => 'ER', 'code_alpha3' => 'ERI', 'name_en' => 'Eritrea', 'name_ar' => 'إريتريا'],

            // South American Countries
            ['code' => 'BR', 'code_alpha3' => 'BRA', 'name_en' => 'Brazil', 'name_ar' => 'البرازيل'],
            ['code' => 'AR', 'code_alpha3' => 'ARG', 'name_en' => 'Argentina', 'name_ar' => 'الأرجنتين'],
            ['code' => 'CL', 'code_alpha3' => 'CHL', 'name_en' => 'Chile', 'name_ar' => 'تشيلي'],
            ['code' => 'CO', 'code_alpha3' => 'COL', 'name_en' => 'Colombia', 'name_ar' => 'كولومبيا'],
            ['code' => 'PE', 'code_alpha3' => 'PER', 'name_en' => 'Peru', 'name_ar' => 'بيرو'],
            ['code' => 'VE', 'code_alpha3' => 'VEN', 'name_en' => 'Venezuela', 'name_ar' => 'فنزويلا'],
            ['code' => 'UY', 'code_alpha3' => 'URY', 'name_en' => 'Uruguay', 'name_ar' => 'أوروغواي'],
            ['code' => 'PY', 'code_alpha3' => 'PRY', 'name_en' => 'Paraguay', 'name_ar' => 'باراغواي'],
            ['code' => 'BO', 'code_alpha3' => 'BOL', 'name_en' => 'Bolivia', 'name_ar' => 'بوليفيا'],
            ['code' => 'EC', 'code_alpha3' => 'ECU', 'name_en' => 'Ecuador', 'name_ar' => 'الإكوادور'],

            // Additional Countries
            ['code' => 'MX', 'code_alpha3' => 'MEX', 'name_en' => 'Mexico', 'name_ar' => 'المكسيك'],
            ['code' => 'CU', 'code_alpha3' => 'CUB', 'name_en' => 'Cuba', 'name_ar' => 'كوبا'],
            ['code' => 'JM', 'code_alpha3' => 'JAM', 'name_en' => 'Jamaica', 'name_ar' => 'جامايكا'],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}
