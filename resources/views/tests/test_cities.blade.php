<form id="popupForm" method="post" action="{{route('map_subj.points.store')}}">
    <!--selectedCity, selectedDistrict, selectedStreet, houseNumberInput.value-->
    @csrf
    <input type="hidden" name="city" value="1">
    <input type="hidden" name="district" value="13">
    <input type="hidden" name="street" value="Боровая">
    <input type="hidden" name="houseNumber" value="11-13">
    <input type="hidden" name="latitude" value="123.12123">
    <input type="hidden" name="longitude" value="123.1212312">
    <input type="hidden" name="subj_id" value="1">
    <span>Мы здесь 🙂</span><br><br>
    <button type="submit" class="btn btn-sm btn-success">Сохранить</button>
</form>