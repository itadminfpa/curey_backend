<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ChoicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('choices')->insert(['question_id' => 1,
            'choice_no' => 1,
            'choice' => "easy",
            'choice_ar' => "سهل",
            'score' => 20]);

            \DB::table('choices')->insert([ 'question_id' => 1,
            'choice_no' => 2,
            'choice' => "normal",
            'choice_ar' => "عادي",
            'score' => 10]);

            \DB::table('choices')->insert([ 'question_id' => 1,
            'choice_no' => 3,
            'choice' => "difficult",
            'choice_ar' => "صعبة",
            'score' => 0]);

        \DB::table('choices')->insert(['question_id' => 2,
            'choice_no' => 1,
            'choice' => "Yes",
            'choice_ar' => "نعم",
            'score' => 20]);

            \DB::table('choices')->insert([ 'question_id' => 2,
            'choice_no' => 2,
            'choice' => "No",
            'choice_ar' => "لا",
            'score' => 0]);

            \DB::table('choices')->insert([ 'question_id' => 2,
            'choice_no' => 3,
            'choice' => "I'm not sure",
            'choice_ar' => "لست متأكد",
            'score' => 10]);
        \DB::table('choices')->insert(['question_id' => 3,
            'choice_no' => 1,
            'choice' => "easy",
            'choice_ar' => "سهل",
            'score' => 20]);

            \DB::table('choices')->insert([ 'question_id' => 3,
            'choice_no' => 2,
            'choice' => "normal",
            'choice_ar' => "عادي",
            'score' => 10]);

            \DB::table('choices')->insert([ 'question_id' => 3,
            'choice_no' => 3,
            'choice' => "difficult",
            'choice_ar' => "صعبة",
            'score' => 0]);

        \DB::table('choices')->insert(['question_id' => 4 ,
            'choice_no' => 1,
            'choice' => "Yes",
            'choice_ar' => "نعم",
            'score' => 20]);

            \DB::table('choices')->insert([ 'question_id' => 4,
            'choice_no' => 2,
            'choice' => "No",
            'choice_ar' => "لا",
            'score' => 0]);

        \DB::table('choices')->insert(['question_id' => 5 ,
            'choice_no' => 1,
            'choice' => "Very satisfied",
            'choice_ar' => "راضِ جداً",
            'score' => 20]);

            \DB::table('choices')->insert([ 'question_id' => 5,
            'choice_no' => 2,
            'choice' => "satisfied",
            'choice_ar' => "راضي",
            'score' => 15]);

            \DB::table('choices')->insert([ 'question_id' => 5,
            'choice_no' => 3,
            'choice' => "Neutral",
            'choice_ar' => "حيادي",
            'score' => 10]);

            \DB::table('choices')->insert([ 'question_id' => 5,
            'choice_no' => 4,
            'choice' => "Not satisfied",
            'choice_ar' => "غير راضى",
            'score' => 5]);

            \DB::table('choices')->insert([ 'question_id' => 5,
            'choice_no' => 5,
            'choice' => "Very dissatisfied",
            'choice_ar' => "مستاء جدا",
            'score' => 0]);

        \DB::table('choices')->insert(['question_id' => 6 ,
            'choice_no' => 1,
            'choice' => "Yes",
            'choice_ar' => "نعم",
            'score' => 20]);

            \DB::table('choices')->insert([ 'question_id' => 6,
            'choice_no' => 2,
            'choice' => "No",
            'choice_ar' => "لا",
            'score' => 0]);

            //
        \DB::table('choices')->insert(['question_id' => 7,
            'choice_no' => 1,
            'choice' => "Very satisfied",
            'choice_ar' => "راضِ جداً",
            'score' => 20]);

            \DB::table('choices')->insert([ 'question_id' => 7,
            'choice_no' => 2,
            'choice' => "satisfied",
            'choice_ar' => "راضي",
            'score' => 15]);

            \DB::table('choices')->insert([ 'question_id' => 7,
            'choice_no' => 3,
            'choice' => "Neutral",
            'choice_ar' => "حيادي",
            'score' => 10]);

            \DB::table('choices')->insert([ 'question_id' => 7,
            'choice_no' => 4,
            'choice' => "Not satisfied",
            'choice_ar' => "غير راضى",
            'score' => 5]);

            \DB::table('choices')->insert([ 'question_id' => 7,
            'choice_no' => 5,
            'choice' => "Very dissatisfied",
            'choice_ar' => "مستاء جدا",
            'score' => 0]);

            //
        \DB::table('choices')->insert(['question_id' => 8 ,
            'choice_no' => 1,
            'choice' => "Yes",
            'choice_ar' => "نعم",
            'score' => 20]);

            \DB::table('choices')->insert([ 'question_id' => 8,
            'choice_no' => 2,
            'choice' => "No",
            'choice_ar' => "لا",
            'score' => 0]);

            \DB::table('choices')->insert([ 'question_id' => 8,
            'choice_no' => 3,
            'choice' => "I'm not sure",
            'choice_ar' => "لست متأكد",
            'score' => 10]);

            ///

        \DB::table('choices')->insert(['question_id' => 9 ,
            'choice_no' => 1,
            'choice' => "Yes",
            'choice_ar' => "نعم",
            'score' => 20]);

            \DB::table('choices')->insert([ 'question_id' => 9,
            'choice_no' => 2,
            'choice' => "No",
            'choice_ar' => "لا",
            'score' => 0]);

            \DB::table('choices')->insert([ 'question_id' => 9,
            'choice_no' => 3,
            'choice' => "I'm not sure",
            'choice_ar' => "لست متأكد",
            'score' => 10]);

            //
        \DB::table('choices')->insert(['question_id' => 10 ,
            'choice_no' => 1,
            'choice' => "Yes",
            'choice_ar' => "نعم",
            'score' => 20]);

            \DB::table('choices')->insert([ 'question_id' => 10,
            'choice_no' => 2,
            'choice' => "No",
            'choice_ar' => "لا",
            'score' => 0]);

            ////

        \DB::table('choices')->insert(['question_id' => 11,
            'choice_no' => 1,
            'choice' => "Very satisfied",
            'choice_ar' => "راضِ جداً",
            'score' => 20]);

            \DB::table('choices')->insert([ 'question_id' => 11,
            'choice_no' => 2,
            'choice' => "satisfied",
            'choice_ar' => "راضي",
            'score' => 15]);

            \DB::table('choices')->insert([ 'question_id' => 11,
            'choice_no' => 3,
            'choice' => "Neutral",
            'choice_ar' => "حيادي",
            'score' => 10]);

            \DB::table('choices')->insert([ 'question_id' => 11,
            'choice_no' => 4,
            'choice' => "Not satisfied",
            'choice_ar' => "غير راضى",
            'score' => 5]);

            \DB::table('choices')->insert([ 'question_id' => 11,
            'choice_no' => 5,
            'choice' => "Very dissatisfied",
            'choice_ar' => "مستاء جدا",
            'score' => 0]);

            ///

        \DB::table('choices')->insert(['question_id' => 12 ,
            'choice_no' => 1,
            'choice' => "Yes",
            'choice_ar' => "نعم",
            'score' => 20]);

            \DB::table('choices')->insert([ 'question_id' => 12,
            'choice_no' => 2,
            'choice' => "No",
            'choice_ar' => "لا",
            'score' => 0]);

            \DB::table('choices')->insert([ 'question_id' => 12,
            'choice_no' => 3,
            'choice' => "I'm not sure",
            'choice_ar' => "لست متأكد",
            'score' => 10]);

    }
}
