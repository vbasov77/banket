var densityCanvas = document.getElementById("weekday");
var arrWeekday = week.split(',');
var arrDataWeek = dataWeek.split(',');

var densityData = {
    label: 'Уникальные посетители за последние (' + arrWeekday.length + ' дней)',
    data: arrDataWeek
};

var barChart = new Chart(densityCanvas, {
    type: 'bar',
    data: {
        labels: arrWeekday,
        datasets: [densityData]
    }
});