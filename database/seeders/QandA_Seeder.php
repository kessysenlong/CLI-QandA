<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuestionAndAnswer;

class QandA_Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $qanda = [
            ['question' => 'The average person does what thirteen times a day?', 'answer' => 'Laughs'],
            ['question' => 'Which bird is nicknamed The Laughing Jackass?', 'answer' => 'Kookaburra'],
            ['question' => 'From what grain is the Japanese spirit Sake made?', 'answer' => 'Rice'],
            ['question' => 'True or false: You can sneeze in your sleep', 'answer' => 'False'],
            ['question' => 'What is Scooby Doo’s full name?', 'answer' => 'Scoobert Doo'],
            ['question' => 'What is the smallest planet in our solar system?', 'answer' => 'Mercury'],
            ['question' => 'Area 51 is located in which US state?', 'answer' => 'Nevada'],
            ['question' => 'From which country does Gouda cheese originate?', 'answer' => 'Netherlands'],
            ['question' => 'A group of ravens is known as?', 'answer' => 'Unkindness'],
            ['question' => 'The unicorn is the national animal of which country?', 'answer' => 'Scotland']
        ];

        foreach ($qanda as $question) {
           QuestionAndAnswer::create(['question' => $question['question'], 'answer' => $question['answer']]);
        }
    }
}
