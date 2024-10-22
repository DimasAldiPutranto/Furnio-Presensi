<div>
    <div class="container mx-auto max-w-sm">
        <div class="bg-[#537FE7]  shadow-2xl">
            <div class="grid grid-cols-1 gap-6 ">
                <div class="bg-[#537FE7] p-4 rounded-t-lg">
                    <div class="flex items-center gap-3 mb-3">
                        <img src="{{ asset('img/luffy.jpeg') }}" alt="" class="w-14 h-14 rounded-full">
                          <div>
                            <h3 class="text-lg font-semibold text-white">
                                {{ Auth::user()->name }} 
                            </h3>
                            {{-- <h5 class="font-sembolg text-white text-sm">
                                {{ Auth::user()->id }}
                            </h5> --}}
                          </div>
                        
                    </div>
                    <div class="bg-white p-4 rounded-t-lg mt-3 text-xl text-center shadow-lg">
                        <p class="text-gray-500 ">
                            {{ $schedule->office->name }}
                        </p>
                        <p class="text-gray-500 mb-3">
                            {{ now()->format('l, d F Y') }}
                        </p>
                        <hr>
                        <hr>
                        <p class="mt-3">
                            {{ $schedule->shift->name }} <br>
                            <p class="font-semibold text-['20px']">
                                {{ $schedule->shift->start_time }} AM - {{ $schedule->shift->end_time }} PM
                            </p>
                        </p>
                        @if($schedule->is_wfa)
                        <p class="text-green-500 mb-3"><strong>Status :</strong>WFA</p>
                        @else
                        <p><strong>Status :</strong>WFO</p>
                        @endif
                        <hr>
                        <hr>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-3">
                            <div class="bg-[#537FE7] w-36 h-16 rounded-lg text-center content-center text-white font-semibold">
                                <h2 class="text-lg font-semibold">Clock In</h2>
                                <p>{{ $attendance ? $attendance->start_time : '-' }}</p>
                            </div>
                            <div class="bg-[#537FE7] w-36 h-16 rounded-lg text-center content-center text-white font-semibold">
                                <h2 class="text-lg font-semibold">Clock Out</h2>
                                <p>{{ $attendance ? $attendance->end_time : '-' }}</p>
                            </div>
                        </div>
                    </div>
                    
                   
                </div>
                <div class="bg-white rounded-t-[16px]">
                    <h2 class="font-bold text-3xl text-center mb-3 mt-6 ">Presensi</h2>
                        <div id="map" class="mb-4 border border-gray-400 px-3" wire:ignore> 
                        </div>
                            @if (session()->has('eror'))    
                            <div style="color: Red; padding: 10px; border: 2px solid Red; background-color: #fdd ">
                                {{ session('error') }}
                            </div>

                            @endif
                            <form class="row g-3 mt-3 p-4" wire:submit="store" enctype="multipart/form-data">
                                <button type="button" onclick="tagLocation()" class="px-4 py-2 bg-blue-500 text-white rounded">
                                    Tag Location
                                </button>
                                @if($insideRadius)
                                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded">
                                    Submit Presensi
                                </button>
                            @endif
                            </form>
                           
                    </div>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        let map;
        let lat;
        let lng;
        const office = [{{ $schedule->office->latitude }}, {{ $schedule->office->longitude }}];
        const radius = {{ $schedule->office->radius }};
        let component;
        let marker;


        document.addEventListener('livewire:initialized', function() {
            component = @this;
            map = L.map('map').setView([{{ $schedule->office->latitude }}, {{ $schedule->office->longitude }}], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'). addTo(map);
        
        const circle = L.circle(office,{
            color : 'red',
            fillColor : '#f03',
            fillOpacity : 0.5,
            radius : radius
        }). addTo(map);
        })
      

        function tagLocation() {
            if(navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position)=> {
                    lat = position.coords.latitude;
                    lng = position.coords.longitude;

                    if(marker) {
                        map.removeLayer(marker);
                    }
                    
                    marker = L.marker([lat,lng]).addTo(map);
                    map.setView([lat,lng], 13);

                    if (isWithinRadius(lat, lng, office, radius)) {
                        component.set('insideRadius', true); 
                        component.set('latitude', lat);
                        component.set('longitude', lng);
                    } else {
                        alert('Yahh!! Anda Masih Diluar Radius');
                    }
                })
            } 
        }

        function isWithinRadius(lat, lng, center, radius) {
            const is_wfa = {{ $schedule->is_wfa }}
            if (is_wfa) {
                return true;
            } else {
                let distance =map.distance([lat, lng], center);
                return distance <= radius;
            }
        }
    </script>
</div>
