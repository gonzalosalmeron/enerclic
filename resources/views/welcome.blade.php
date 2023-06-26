<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Enerclic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>

<body class="antialiased">
    <section class="container-lg py-4">
        <h1>Enerclic</h1>
        <div class="p-4">
            <form class="row" style="max-width: 500px;">
                @csrf
                <div class="col">
                    <input type="date" name="date" class="form-control" pattern="\d{4}-\d{2}-\d{2}">
                </div>
                <div class="col">
                    <button type="button" onclick="showCharts()" class="btn btn-primary col">Calculate</button>
                </div>
            </form>

            <div class="row mt-4">
                <div class="col-3">
                    <div class="border p-3">
                        <h4>Contador 1</h4>
                        <div class="row">
                            <h5 id="contador1_avg_power">Power: <span class="text-primary">0</span> kw</h5>
                            <h5 id="contador1_avg_energy">Energy: <span class="text-primary">0</span> kwh</h5>
                        </div>
                    </div>
                    <div class="mt-4 border p-3">
                        <h4>Contador 2</h4>
                        <div class="row">
                            <h5 id="contador2_avg_power">Power: <span class="text-primary">0</span> kw</h5>
                            <h5 id="contador2_avg_energy">Energy: <span class="text-primary">0</span> kwh</h5>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div id="minute_power_chart"></div>
                    <div class="mt-4" id="hour_energy_chart"></div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.slim.min.js" integrity="sha256-tG5mcZUtJsZvyKAxYLVXrmjKBVLd6VpVccqz/r4ypFE=" crossorigin="anonymous"></script>


    <script>
        const fetchData = async () => {
            const day = $('input[name=date]').val();
            if (day == '') return

            const response = await fetch('/api/datos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'url': '/api/datos',
                    "X-CSRF-Token": $('input[name=_token]').val()
                },
                body: JSON.stringify({
                    date: day
                })
            })
            const data = await response.json();

            return data;
        }

        const showCharts = async () => {
            const data = await fetchData();
            if (data.length == 0) return

            $('#contador1_avg_power > span').text(`${data['contador1']['avg_power']}`);
            $('#contador1_avg_energy > span').text(`${data['contador1']['avg_energy']}`);
            $('#contador2_avg_power > span').text(`${data['contador2']['avg_power']}`);
            $('#contador2_avg_energy > span').text(`${data['contador2']['avg_energy']}`);

            Highcharts.chart('minute_power_chart', {
                chart: {
                    zoomType: 'x'
                },
                title: {
                    text: 'Minute power data',
                },
                xAxis: {
                    type: 'datetime',
                    tickInterval: 0.1
                },
                yAxis: {
                    title: {
                        text: 'Pac(kw)'
                    }
                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    area: {
                        fillColor: {
                            stops: [
                                [0, Highcharts.getOptions().colors[0]],
                                [1, Highcharts.color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                            ]
                        },
                        marker: {
                            radius: 2
                        },
                        lineWidth: 1,
                        states: {
                            hover: {
                                lineWidth: 1
                            }
                        },

                    }
                },

                series: [{
                    type: 'area',
                    data: data['contador1']['data']
                }, {
                    type: 'area',
                    data: data['contador2']['data']
                }]
            });

            Highcharts.chart('hour_energy_chart', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Hourly energy data'
                },
                xAxis: {
                    crosshair: true,
                    tickInterval: 0.5
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Energy (kwh)'
                    }
                },
                series: [{
                    data: data['contador1']['avg_energy_per_hour'],
                    name: 'Contador 1'
                }]
            });
        }
    </script>
</body>

</html>
