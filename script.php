<?php

function getCurrencies()
{

    $ch = curl_init('https://www.isbank.com.tr/_vti_bin/DV.Isbank/FinancialData/FinancialDataService.svc/GetFinancialData?_=' . time());
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true
    ]);
    $output = curl_exec($ch);
    curl_close($ch);

    $output = json_decode($output, true);
    $results = $output['Data']['Market'];
    $eur = $results[1];
    $usd = $results[0];

    return [
        'eur' => $eur['FxRateSell'],
        'usd' => $usd['FxRateSell'],
        'try' => 1
    ];
}

class Convert {

    public $banknotes = [
        'eur' => [500, 200, 100, 50, 20, 10, 5, 2, 1, '0.5', '0.2', '0.1', '0.05', '0.02', '0.01'],
        'usd' => [100, 50, 20,10,5,1,'0.5', '0.25', '0.1', '0.05', '0.01'],
        'try' => [200, 100, 50, 20, 10, 5, 1, '0.5', '0.25', '0.1', '0.05', '0.01']
    ];

    public $toCurrency;
    public static $amount;
    public static $amount_converted;
    
    public static function amount($amount){
        self::$amount = $amount;
        return new self();
    }

    public function calculate(){
        return array_reduce($this->banknotes[$this->toCurrency], function($acc, $value){
            if (fmod(self::$amount, $value) < self::$amount){
                $total = floor(self::$amount / $value);
                $acc[$value] = $total;
                self::$amount -= $value * $total;
            }
            return $acc;
        });
    }

    public function to($toCurrency)
    {
        $this->toCurrency = $toCurrency;
        self::$amount_converted = self::$amount = self::$amount / getCurrencies()[$toCurrency];
        return $this->calculate();
    }

    public static function getConvertedAmount(){
        $amount = explode('.', self::$amount_converted);
        return $amount[0] . (isset($amount[1]) ? '.' . substr($amount[1], 0, 2) : null);
    }

    public static function getCurrencySymbol($currency)
    {
        $symbols = [
            'eur' => '€',
            'usd' => '$',
            'try' => '₺'
        ];
        return $symbols[$currency];
    }

}

?>