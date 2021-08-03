<?php

declare(strict_types=1);

use App\Helper\TelegramBotHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('bots', function (Blueprint $table) {
            $table->id();
            $table->enum('command', TelegramBotHelper::BOT_COMMANDS);
            $table->integer('step_id');
            $table->integer('next_step_id')->nullable();
            $table->string('bot_step', 255);
            $table->string('user_step', 255);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('bots');
    }
}
