import React from 'react';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'leaflet/dist/leaflet.css';
import './App.css';
import Geofencing from './components/Geofencing';

const App = () => {
  return (
    <div className="App">
      <Geofencing />
    </div>
  );
};

export default App;
