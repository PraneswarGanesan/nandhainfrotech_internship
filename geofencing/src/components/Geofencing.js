import React, { useState } from 'react';
import axios from 'axios';
import { MapContainer, TileLayer, Marker, Polygon, useMapEvents, useMap } from 'react-leaflet';
import { AsyncTypeahead } from 'react-bootstrap-typeahead';
import 'leaflet/dist/leaflet.css';
import 'react-bootstrap-typeahead/css/Typeahead.css';
import L from 'leaflet';
import './Geofencing.css'; // Import the CSS file for custom styles

// Custom marker icon
const customMarkerIcon = new L.Icon({
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
  shadowSize: [41, 41]
});

const MapClickHandler = ({ points, setPoints }) => {
  useMapEvents({
    click(e) {
      const { lat, lng } = e.latlng;
      setPoints([...points, { lat, lng }]);
      savePointToServer(lat, lng, "additional info"); // Pass additional data here
      console.log('Clicked Point:', { latitude: lat, longitude: lng });
    }
  });
  return null;
};

const savePointToServer = async (lat, lng, additionalData) => {
  try {
    const response = await axios.post('http://localhost/backend/savepoint.php', {
      latitude: lat,
      longitude: lng,
      additional_data: additionalData
    });
    console.log('Point saved successfully:', response.data);
  } catch (error) {
    console.error('Error saving point:', error);
  }
};

const Geofencing = () => {
  const [latitude, setLatitude] = useState(11.0067); // Default latitude
  const [longitude, setLongitude] = useState(76.9614); // Default longitude
  const [points, setPoints] = useState([]); // No initial points
  const [options, setOptions] = useState([]);
  const [isLoading, setIsLoading] = useState(false);

  const handleSearch = async (query) => {
    setIsLoading(true);
    const res = await axios.get(`https://nominatim.openstreetmap.org/search?q=${query}&format=json`);
    setOptions(res.data);
    setIsLoading(false);
  };

  const handleLocationSelect = (selected) => {
    if (selected.length > 0) {
      const { lat, lon } = selected[0];
      const newLat = parseFloat(lat);
      const newLon = parseFloat(lon);
      setLatitude(newLat);
      setLongitude(newLon);
      setPoints([{ lat: newLat, lng: newLon }]);
    }
  };

  const FlyToLocation = ({ latitude, longitude }) => {
    const map = useMap();
    map.flyTo([latitude, longitude], 14); // Fly to the new location with zoom level 14
    return null;
  };

  return (
    <div>
      <h1>Geofencing Application</h1>
      <div className='latlong'>
        <p>Latitude: {latitude}</p>
        <p>Longitude: {longitude}</p>
        <p>Points: {points.length}</p>
      </div>
      <div className="search-bar-container">
        <AsyncTypeahead
          id="search-bar"
          isLoading={isLoading}
          labelKey="display_name"
          onSearch={handleSearch}
          options={options}
          placeholder="Search for a location..."
          onChange={handleLocationSelect}
          renderMenuItemChildren={(option) => (
            <div key={option.place_id}>
              {option.display_name}
            </div>
          )}
        />
      </div>
      
      <MapContainer center={[latitude, longitude]} zoom={12} style={{ height: '550px', width: '200vh' }} className='map'>
        <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
        <FlyToLocation latitude={latitude} longitude={longitude} />
        <MapClickHandler points={points} setPoints={setPoints} />
        {points.map((point, idx) => (
          <Marker key={idx} position={[point.lat, point.lng]} icon={customMarkerIcon} />
        ))}
        {points.length > 2 && <Polygon positions={points} pathOptions={{ fillColor: 'blue', fillOpacity: 0.4 }} />}
      </MapContainer>
    </div>
  );
};

export default Geofencing;
