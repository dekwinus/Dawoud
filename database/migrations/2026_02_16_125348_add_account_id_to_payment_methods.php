<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->integer('account_id')->nullable()->after('name');
        });

        // Seed Accounts and Link Payment Methods
        // Ensure "Cash", "Inistapay", "E-Wallet" accounts exist
        $accounts = [
            ['name' => 'Cash Account', 'num' => '1001', 'method_id' => 2],    // Cash
            ['name' => 'Inistapay Account', 'num' => '1002', 'method_id' => 1], // Inistapay
            ['name' => 'E-Wallet Account', 'num' => '1003', 'method_id' => 3],  // E-Wallet
        ];

        foreach ($accounts as $acc) {
            // Find or create account
            $account = DB::table('accounts')->where('account_name', $acc['name'])->first();
            if (!$account) {
                $now = now();
                $id = DB::table('accounts')->insertGetId([
                    'account_num' => $acc['num'],
                    'account_name' => $acc['name'],
                    'initial_balance' => 0,
                    'balance' => 0,
                    'note' => 'Auto-created for Payment Method',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            } else {
                $id = $account->id;
            }

            // Link Payment Method
            DB::table('payment_methods')->where('id', $acc['method_id'])->update(['account_id' => $id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('account_id');
        });
    }
};
