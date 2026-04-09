<form id="popupForm" action="{{route('map_subj.points.store')}}" method="post">
    @csrf

    <input type="hidden" name="city_id" value="1">
    <input type="hidden" name="district_id" value="20">
    <input type="hidden" name="street" value="улица Садовая">
    <input type="hidden" name="houseNumber" value="11">
    <input type="hidden" name="latitude" value="60.06635400">
    <input type="hidden" name="longitude" value="30.46451600">
    <input type="hidden" name="subj_id" value="5">
    <input type="hidden" name="obj_id" value="2">
    <span><b>Зирка</b></span><br>
    <span>Мы здесь 🙂</span><br>
    <button type="submit" class="btn btn-sm btn-success">Сохранить</button>
</form>