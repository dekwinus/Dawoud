<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add the new column if it doesn't exist
        if (!Schema::hasColumn('payrolls', 'payment_method_id')) {
            Schema::table('payrolls', function (Blueprint $table) {
                $table->integer('payment_method_id')->nullable()->after('payment_method');
            });
        }

        // Step 2: Update values based on payment_method if payment_method still exists
        if (Schema::hasColumn('payrolls', 'payment_method')) {
            $mapping = [
                'credit card' => 1,
                'Cash' => 2,
                'cheque' => 3,
                'tpe' => 4,
                'Western Union' => 5,
                'bank transfer' => 6,
                'other' => 7,
            ];

            foreach ($mapping as $payment_method => $id) {
                DB::table('payrolls')
                    ->where('payment_method', $payment_method)
                    ->update(['payment_method_id' => $id]);
            }

            // Step 3: Remove the old payment_method column
            Schema::table('payrolls', function (Blueprint $table) {
                $table->dropColumn('payment_method');
            });
        }

        // Step 4: Add foreign key constraint if it doesn't already exist
        // Checking if the index/fk exists to avoid "duplicate key name" errors
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'payrolls' AND TABLE_SCHEMA = '" . env('DB_DATABASE') . "' AND CONSTRAINT_NAME = 'payrolls_payment_method_id_foreign'");
        
        if (empty($foreignKeys)) {
            Schema::table('payrolls', function (Blueprint $table) {
                $table->foreign('payment_method_id')
                    ->references('id')
                    ->on('payment_methods');
            });
        }
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // Check if foreign key exists before dropping
            $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'payrolls' AND TABLE_SCHEMA = '" . env('DB_DATABASE') . "' AND CONSTRAINT_NAME = 'payrolls_payment_method_id_foreign'");
            
            if (!empty($foreignKeys)) {
                $table->dropForeign(['payment_method_id']);
            }

            // check if payment_method column missing before adding back
            if (!Schema::hasColumn('payrolls', 'payment_method')) {
                $table->string('payment_method')->nullable();
            }

            // check if payment_method_id exists before dropping
            if (Schema::hasColumn('payrolls', 'payment_method_id')) {
                // We need to drop the FK before dropping the column, which we checked above
                $table->dropColumn('payment_method_id');
            }
        });
    }
};
