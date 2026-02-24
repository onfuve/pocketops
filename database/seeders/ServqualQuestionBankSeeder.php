<?php

namespace Database\Seeders;

use App\Models\ServqualDimension;
use App\Models\ServqualQuestionBank;
use Illuminate\Database\Seeder;

class ServqualQuestionBankSeeder extends Seeder
{
    public function run(): void
    {
        $dimensions = [
            ['code' => 'tangibles',      'name' => 'Tangibles',      'name_fa' => 'ملموسات',         'sort' => 1],
            ['code' => 'reliability',    'name' => 'Reliability',    'name_fa' => 'قابلیت اطمینان',   'sort' => 2],
            ['code' => 'responsiveness', 'name' => 'Responsiveness', 'name_fa' => 'پاسخگویی',        'sort' => 3],
            ['code' => 'assurance',      'name' => 'Assurance',      'name_fa' => 'اطمینان',          'sort' => 4],
            ['code' => 'empathy',        'name' => 'Empathy',        'name_fa' => 'همدلی',            'sort' => 5],
        ];

        $questions = [
            'tangibles' => [
                ['The shop environment looked professional.', 'محیط مغازه حرفه‌ای به نظر می‌رسید.'],
                ['Tools and equipment appeared modern.', 'ابزار و تجهیزات مدرن به نظر می‌رسید.'],
                ['The workspace was clean and organized.', 'محیط کار تمیز و منظم بود.'],
                ['Materials and parts looked quality.', 'قطعات و مواد باکیفیت به نظر می‌رسیدند.'],
                ['The waiting area was comfortable.', 'محیط انتظار راحت بود.'],
            ],
            'reliability' => [
                ['The repair was done correctly.', 'تعمیر به درستی انجام شد.'],
                ['The device works as promised.', 'دستگاه طبق وعده کار می‌کند.'],
                ['My device works reliably after repair.', 'دستگاه من بعد از تعمیر پایدار کار می‌کند.'],
                ['I feel confident the issue will not return soon.', 'مطمئنم مشکل به این زودی برنمی‌گردد.'],
                ['The fix appears durable.', 'تعمیر ماندگار به نظر می‌رسد.'],
            ],
            'responsiveness' => [
                ['They handled my request quickly.', 'درخواست من را سریع رسیدگی کردند.'],
                ['Support reacted promptly.', 'پشتیبانی به‌موقع واکنش نشان داد.'],
                ['The repair time was reasonable.', 'زمان تعمیر معقول بود.'],
                ['My issue was handled quickly.', 'مشکل من سریع رسیدگی شد.'],
                ['I did not wait longer than expected.', 'بیش از حد انتظار معطل نشدم.'],
            ],
            'assurance' => [
                ['I trust this business.', 'به این کسب‌وکار اعتماد دارم.'],
                ['I felt safe leaving my device.', 'با خیال راحت دستگاه را گذاشتم.'],
                ['The technician knew what they were doing.', 'تکنسین می‌دانست چه کار می‌کند.'],
                ['The issue was properly fixed.', 'مشکل به درستی برطرف شد.'],
                ['They were honest about the problem.', 'در مورد مشکل صادق بودند.'],
            ],
            'empathy' => [
                ['They understood my concern.', 'نگرانی من را درک کردند.'],
                ['I felt personally respected.', 'احساس احترام شخصی کردم.'],
                ['The problem was explained clearly.', 'مشکل به وضوح توضیح داده شد.'],
                ['I understood what was done.', 'متوجه شدم چه کاری انجام شد.'],
                ['They listened to my description.', 'به توضیح من گوش دادند.'],
            ],
        ];

        foreach ($dimensions as $d) {
            $dim = ServqualDimension::updateOrCreate(
                ['code' => $d['code']],
                ['name' => $d['name'], 'name_fa' => $d['name_fa'], 'sort' => $d['sort']]
            );

            $items = $questions[$d['code']] ?? [];
            foreach ($items as $i => $q) {
                ServqualQuestionBank::updateOrCreate(
                    [
                        'dimension_id' => $dim->id,
                        'text' => $q[0],
                    ],
                    [
                        'text_fa' => $q[1] ?? null,
                        'weight' => 1,
                        'is_reverse_scored' => false,
                        'sort' => $i + 1,
                    ]
                );
            }
        }
    }
}
