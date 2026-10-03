var phoneCanvas = document.getElementById("phoneClicks");
var arrDays14 = days14.split(',');
var arrDataPhone = dataPhone.split(',');

var phoneData = {
    label: 'Клики по телефонам за последние (' + arrDays14.length + ' дней)',
    data: arrDataPhone
};

var phoneChart = new Chart(phoneCanvas, {
    type: 'bar',
    data: {
        labels: arrDays14,
        datasets: [phoneData]
    }
});
