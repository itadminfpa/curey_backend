<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class QuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //ignored
        //2, 5 , 15
        \DB::table('questions')->insert(
            [
                ['question' => 'How easy was it to schedule an appointment with the hospital or
                medical service provider?','question_ar' => 'ما مدى سهولة تحديد موعد مع المستشفى أو مزود الخدمة الطبية؟'],
                ['question' => 'Was the medical case and treatment protocol adequately explained
                by the medical service provider?','question_ar' => 'هل تم شرح الحالة الطبية وبروتوكول العالج بشكل كاف من قبل مقدم الخدمة الطبية؟ '],
                ['question' => 'In case of requesting a medical report, how easy was it to take
                medical report for the case?','question_ar' => 'في حالة طلب تقرير طبي ، ما مدى سهولة أخذ التقرير الطبي للحالة؟ '],
                ['question' => 'On getting to the hospital or medical service provider, was a primary
                patient health history check conducted?','question_ar' => 'عند الوصول إلى المستشفى أو مقدم الخدمة الطبية ، هل تم إجراء فحص أولي للتاريخ
                الصحي للمريض؟'],
                ['question' => 'How satisfied were you with the hospital or medical service provider
                assessment of your case?','question_ar' => 'ما مدى رضاك عن تقييم المستشفى أو مقدم الخدمة الطبية لحالتك؟'],
                ['question' => 'In the case of using medicines or medical tools, were they available
                with the medical service provider, or was it required to purchase
                them from outside the hospital or the medical service provider?','question_ar' => 'في حالة استخدام األدوية أو األدوات الطبية ، هل كانت متوفرة لدى مقدم الخدمة الطبية ،
                أم كان مطلوبًا شرائها من خارج المستشفى أو مقدم الخدمة الطبية؟'],
                ['question' => "How satisfied are you with the cleanliness and appearance of
                hospital or medical service provider's facility?",'question_ar' => 'ما مدى رضاك عن نظافة ومظهر المستشفى أو مرفق مزود الخدمة الطبية؟'],
                ['question' => "Dose the hospital or medical service provider's staff use infection
                prevention tools to protect from viruses such as COVID-19?",'question_ar' => 'هل يستخدم طاقم المستشفى أو مقدم الخدمة الطبية أدوات الوقاية من العدوى للحماية من
                الفيروسات مثل 19-COVID؟'],
                ['question' => 'Did the doctor or medical service provider use new, sterile, or packaged tools during your health examination?','question_ar' => 'هل استخدم الطبيب أو مقدم الخدمة الطبية أدوات جديدة أو معقمة أو مغلفة أثناء فحصك
                الصحي؟ '],
                ['question' => 'Was an invoice issued for the cost of the service provided?','question_ar' => 'هل تم إصدار فاتورة بتكلفة الخدمة المقدمة؟'],
                ['question' => 'Are you satisfied with the cost for the service provided?','question_ar' => 'هل أنت را ٍض عن تكلفة الخدمة المقدمة؟'],
                ['question' => 'Is the price of the tools or medicines used for your case by the
                hospital or medical service providers are appropriate?','question_ar' => 'هل سعر األدوات أو األدوية المستخدمة لحالتك من قبل المستشفى أو مقدمي الخدمة
                الطبية مناسب؟'],
            ]
        );
    }
}
