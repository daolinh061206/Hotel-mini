<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// MySQL 8.0.16+; chay sau 10 migration cua project goc.
return new class extends Migration {
    public function up(): void
    {
        // Ngan hai giao dich success cho cung mot booking, cho phep nhieu failed.
        DB::statement(<<<'SQL'
            ALTER TABLE payments
              ADD COLUMN successful_booking_id BIGINT UNSIGNED GENERATED ALWAYS AS
                (CASE WHEN status = 'success' THEN booking_id ELSE NULL END) STORED,
              ADD UNIQUE KEY uq_one_successful_payment (successful_booking_id),
              ADD CONSTRAINT chk_paid_at_when_success
                CHECK (status <> 'success' OR paid_at IS NOT NULL)
        SQL);

        Schema::create('card_payment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->unique()->constrained('payments')->restrictOnDelete();
            $table->enum('card_brand', ['VISA', 'MASTERCARD', 'JCB']);
            $table->char('card_last4', 4);
            $table->string('cardholder_name', 120);
            $table->enum('simulation_result', ['approved', 'declined', 'insufficient_funds']);
            $table->string('authorization_code', 32)->nullable();
            $table->dateTime('processed_at');
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
        DB::statement("ALTER TABLE card_payment_details ADD CONSTRAINT chk_card_last4 CHECK (card_last4 REGEXP '^[0-9]{4}$')");
        DB::statement(<<<'SQL'
            ALTER TABLE card_payment_details
              ADD CONSTRAINT chk_fake_auth_code CHECK (
                (simulation_result = 'approved' AND authorization_code IS NOT NULL)
                OR (simulation_result <> 'approved' AND authorization_code IS NULL)
              )
        SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('card_payment_details');
        DB::statement('ALTER TABLE payments DROP CHECK chk_paid_at_when_success');
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('uq_one_successful_payment');
            $table->dropColumn('successful_booking_id');
        });
    }
};
