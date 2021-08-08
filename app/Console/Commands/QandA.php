<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\Helpers\CreateQuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use App\Models\QuestionAndAnswer;

class QandA extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qanda:interactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Launches menu for interactive Question and Answer practice program';


    /* 
    QandA public variables
    */
    protected $table = null;
    protected $correct_answers = 0;
    protected $all_questions = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->resetPractice();
        $this->showMenu();
    }

    public function showMenu()
    {
        $this->logo();
        $selected = $this->choice('Select option. (Enter option index to select)', ['Create a question', 'List all questions', 'Practice', 'Stats', 'Reset', 'Exit']);
        $this->resolveMenuItems($selected);
    }


    protected function resolveMenuItems($selected)
    {
        switch ($selected) {
                // creat and store new question with its answer
            case 'Create a question':
                $this->createNewQuestion();
                break;

                // list all saved questions 
            case 'List all questions':
                $this->listQuestions();
                break;

                // practice questions
            case 'Practice':
                $this->practiceQuestions();
                break;
                // show user QandA stats
            case 'Stats':
                $this->renderStatsTable();
                break;
                // erase all practice progress and allow a fresh start
            case 'Reset':
                $reset = $this->confirm('Reset QandA program?');
                $reset == true ? $this->handle() : $this->showMenu();
                break;
                // exits QandA program
            case 'Exit':
                // $this->info('Exiting...');
                exit('Exiting...');
                break;
            default:
                $this->showMenu();
                break;
        }
    }

    /* 
    Adds new question and its answer to db.
     */
    protected function createNewQuestion()
    {
        $question = $this->ask('What is your question');
        $answer = $this->ask('What is the answer');
        if ($this->questionIsValid($question, $answer)) {
            $this->info('Question added successfully');
            $this->info('Question: ' . $question . ' Answer: ' . $answer);
            $this->showMenu();
        } else {
            $this->error('This question already exists, try again');
            $this->showMenu();
        }
    }

    function questionIsValid($question, $answer)
    {
        /*  Validate new question
        if first question created - valid
        if question doesn't already exist - valid 
        Store in db and refresh model
        */
        if ($this->all_questions->isEmpty() || is_null($this->all_questions->where('question', $question)->first())) {
            $new_question = QuestionAndAnswer::create(['question' => $question, 'answer' => $answer]);
            $this->refreshQandA();
            return true;
        } else {
            return false;
        }
    }


    /*  
    Lists all stored questions and answers in db.
    */
    protected function listQuestions()
    {
        if ($this->all_questions->isEmpty()) {
            $this->error('You have not saved any questions yet');
            if ($this->confirm('Create new questions now?')) {
                $this->createNewQuestion();
            } else {
                $this->showMenu();
            }
        } else {
            $table = new Table($this->output);
            $table->setHeaders(['Id', 'Question', 'Answer']);
            $table->setRows(
                $this->all_questions->map->only(['id', 'question', 'answer'])->toArray(),
            );

            $table->setFooterTitle('Total Questions: ' . count($this->all_questions));
            $table->render();
        }

        if ($this->confirm('Return to menu?')) {
            $this->showMenu();
        } else {
            $this->listQuestions();
        }
    }

    /* 
    Allows users practice stored questions
    */
    protected function practiceQuestions()
    {
        if ($this->all_questions->isEmpty()) {
            $this->error('You have not saved any questions yet');
            if ($this->confirm('Create new questions now?')) {
                $this->createNewQuestion();
            } else {
                $this->showMenu();
            }
        } else {
            $this->askQuestions();
        }
    }



    /*  
    Begin interactive QandA session
    */
    protected function askQuestions()
    {
        $this->renderPracticeTable();
        $selected_question = $this->ask('Which question would you like to answer? (Enter question id to select)');
        $question = $this->all_questions->where('id', $selected_question)->first();

        // stop user from answering correctly answered questions, validate selected question option
        if ($question == null) {
            $this->error('Invalid option');
            $this->iterateQuestionPractice();
        } elseif ($question->status == 'Correct') {
            $this->error('You have already answered this question');
            $this->iterateQuestionPractice();
        }

        $this->info($question->question);
        $user_answer = $this->ask('Answer ');

        if (strtolower($user_answer) == strtolower($question->answer)) {
            $this->correct_answers += 1;
            $question->status = 'Correct';
            $this->info('Correct');
        } else {
            $question->status = 'Incorrect';
            $this->error('Incorrect');
        }

        /*
        //  Option 1
        // Uncomment this for continuous practice, no confirmation required from user to continue QandA practice. 
        // *Option to return to main menu and stats available on correct completion of all questions and/or attempting all questions.
        
        $this->iterateQuestionPractice();
        */

        /* 
        // Option 2
        // Confirms user wants to continue practicing before presenting practice questions 
        // *Gives user the option to check stats or return to main menu before continuing practice
        */
        $continue_qanda = $this->confirm('Continue practicing?');
        if ($continue_qanda) {
            $this->iterateQuestionPractice();
        } else {
            $this->showMenu();
        }
    }



    /* 
    Render new or updated Questions practice table 
    */
    protected function renderPracticeTable()
    {
        $this->table->setHeaders(['Id', 'Question', 'Status']);
        $this->table->setRows(
            $this->all_questions->map->only(['id', 'question', 'status'])->toArray(),
        );
        $progress = floor(($this->correct_answers / count($this->all_questions)) * 100);
        $this->table->setFooterTitle('Progress: ' . $progress . '%');
        $this->table->render();
    }



    /* 
    Loops the interactive QandA session with conditions
    */
    protected function iterateQuestionPractice()
    {
        $answered = $this->all_questions->where('status', '!=', 'Not Answered');
        // option to return to stats page if user has ATTEMPTED ALL questions
        if (count($answered) == count($this->all_questions) && $this->correct_answers < count($this->all_questions)) {
            $this->renderPracticeTable();
            $go_to_stats =  $this->confirm('You have answered all questions, go to stats?');
            if ($go_to_stats) {
                $this->renderStatsTable();
            } else {
                $this->askQuestions();
            }
            // option to return to stats page if user has ANSWERED ALL questions CORRECTLY
        } elseif ($this->correct_answers == count($this->all_questions)) {
            $this->renderPracticeTable();
            $go_to_stats =  $this->confirm('You have correctly answered all questions, go to stats?');
            if ($go_to_stats) {
                $this->renderStatsTable();
            } else {
                $this->showMenu();
            }
        }
        //allow user practice all questions until complete  
        else if ($this->correct_answers < count($this->all_questions)) {
            $this->askQuestions();
        }
    }


    /* 
    Render table with user practice stats
    */
    protected function renderStatsTable()
    {
        $total = $this->all_questions == null ? 0 : count($this->all_questions);
        $answered = 0;
        $correct = 0;

        if ($this->correct_answers > 0) {
            $answered = (count($this->all_questions->where('status', '!=', 'Not Answered')) / $total) * 100;
            $correct = (count($this->all_questions->where('status', 'Correct')) / $total) * 100;
        }

        $this->table(
            ['Stats'],
            [
                ['Total Questions', $total],
                ['% Answered', floor($answered) . '%'],
                ['% Answered Correctly', floor($correct) . '%']
            ]

        );
        if ($this->confirm('Return to menu?')) {
            $this->showMenu();
        } else {
            $this->renderStatsTable();
        }
    }


    /*  
    Reset user practice progress and start fresh practice
    */
    protected function resetPractice()
    {
        $this->table = new Table($this->output);
        $this->correct_answers = 0;
        $this->all_questions = QuestionAndAnswer::all();
        foreach ($this->all_questions as $q) {
            $q['status'] = 'Not Answered';
        }
    }



    /* 
    Refresh questions collection after updates
    */
    protected function refreshQandA()
    {
        $qanda = QuestionAndAnswer::all()->fresh();

        if($qanda->isNotEmpty()){
            foreach ($qanda as $q) {
                foreach ($this->all_questions as $question) {
                    if ($q->id == $question->id) {
                        $q->status = is_null($question->status) ? 'Not Answered' : $question->status;
                    }
                }
            }
            $qanda->last()->status = 'Not Answered';
        }
        $this->all_questions = $qanda;
    }

    protected function logo(){
        $this->info("

        #     ____                     _              _____  _           _____                      
        #    / __ \                   | |    /\      / ____|| |         |  __ \                     
        #   | |  | |  __ _  _ __    __| |   /  \    | (___  | |_  _   _ | |  | |  ___    ___  _   _ 
        #   | |  | | / _` || '_ \  / _` |  / /\ \    \___ \ | __|| | | || |  | | / _ \  / __|| | | |
        #   | |__| || (_| || | | || (_| | / ____ \   ____) || |_ | |_| || |__| || (_) || (__ | |_| |
        #    \___\_\ \__,_||_| |_| \__,_|/_/    \_\ |_____/  \__| \__,_||_____/  \___/  \___| \__,_|
        #                                                                                                                                                                                   
                ");
    }
}
