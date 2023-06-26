<?php

namespace App\Http\Controllers;

use App\Models\DMeter;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function datos(Request $request)
    {
        if (is_null($request->date)) return response()->json([]);

        $recived_date = $request->date;

        $d_meter = DMeter::whereDate('datatime', $recived_date)->get();

        if (count($d_meter) == 0) {
            $d_meter = $this->store_data($recived_date);
        };

        $data = $this->adapt_data($d_meter);

        return response()->json($data);
    }

    public function adapt_data($d_meter)
    {
        $data = [];

        // Avg power
        $avg_power_1 = 0;
        $avg_power_2 = 0;

        // Avg energy
        $avg_energy_1 = 0;
        $avg_energy_2 = 0;

        // Last powers
        $last_power1 = 0;
        $last_power2 = 0;

        // Avg energy per hour
        $avg_energy_hour = [];
        foreach ($d_meter as $item) {
            $date = Carbon::parse($item['datatime'])->timestamp * 1000;
            $hour = Carbon::parse($item['datatime'])->format('H');

            $avg_power_1 += $item['power'];
            $avg_power_2 += $item['power2'];

            $avg_energy_1 += $item['energy'];
            $avg_energy_2 += $item['energy2'];

            $data['contador1']['data'][] = [$date, $item['power'] ?? $last_power1];
            $data['contador2']['data'][] = [$date, $item['power2'] ?? $last_power2];

            $last_power1 = $item['power'] ?? $last_power1;
            $last_power2 = $item['power2'] ?? $last_power2;

            if (!isset($avg_energy_hour[$hour])) $avg_energy_hour[$hour]['sum'] = $item['energy'];
            else $avg_energy_hour[$hour]['sum'] += $item['energy'];

            if (!isset($avg_energy_hour[$hour]['count'])) $avg_energy_hour[$hour]['count'] = 1;
            else $avg_energy_hour[$hour]['count']++;
        }

        $avg_energy_contador1 = [];
        foreach ($avg_energy_hour as $hour => $energy) {
            $avg_energy_hour[$hour]['avg'] = round($energy['sum'] / $energy['count'], 2);
            $avg_energy_contador1[] = [$hour, $avg_energy_hour[$hour]['avg']];
        }

        $data['contador1']['avg_power'] = round($avg_power_1 / count($d_meter), 2);
        $data['contador2']['avg_power'] = round($avg_power_2 / count($d_meter), 2);
        $data['contador1']['avg_energy'] = round($avg_energy_1 / count($d_meter), 2);
        $data['contador2']['avg_energy'] = round($avg_energy_2 / count($d_meter), 2);

        $data['contador1']['avg_energy_per_hour'] = $avg_energy_contador1;

        return $data;
    }

    public function store_data($received_date)
    {
        $date = Carbon::createFromFormat('Y-m-d', $received_date)->startOfDay();
        $registers = 60 * 24;

        $data = [];

        $last_energy = 0;
        $last_energy2 = 0;

        for ($i = 0; $i < $registers; $i++) {
            $datetime = $date->format('Y-m-d H:i:s');

            if ($date->minute > 15) {
                $power = $this->rnd_float(1, 10);
                $power2 = $this->rnd_float(1, 10);

                $energy = $this->calc_energy($last_energy, $power);
                $energy2 = $this->calc_energy($last_energy2, $power2);

                $last_energy = $energy;
                $last_energy2 = $energy2;
            }

            $data[] = [
                'datatime' => $datetime,
                'power' => $power ?? null,
                'power2' => $power2 ?? null,
                'energy' => $energy ?? 0,
                'energy2' => $energy2 ?? 0,
            ];

            $date->addMinute();
            $power = null;
            $power2 = null;
        }

        DMeter::insert($data);

        return $data;
    }

    public function rnd_float($min, $max)
    {
        return rand($min, $max * 10) / 10;
    }

    public function calc_energy($last_power, $power)
    {
        return $last_power + ($power / 60);
    }
}
