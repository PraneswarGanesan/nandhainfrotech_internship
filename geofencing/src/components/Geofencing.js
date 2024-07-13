import React, { useState } from 'react';
import axios from 'axios';
import { MapContainer, TileLayer, Marker, Polygon, useMapEvents } from 'react-leaflet';
import 'leaflet/dist/leaflet.css';
import L from 'leaflet';
import convexHull from 'monotone-convex-hull-2d';
import { Stage, Layer, Text, Image, Shape } from 'react-konva';
import useImage from 'use-image';
import { TransformWrapper, TransformComponent } from 'react-zoom-pan-pinch';
import './Geofencing.css';

const customMarkerIcon = new L.Icon({
  iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
  shadowSize: [41, 41]
});

const treeIconUrl = 'https://cdnjs.cloudflare.com/ajax/libs/twemoji/13.0.1/72x72/1f333.png';

const MapClickHandler = ({ points, setPoints }) => {
  const addPoint = (lat, lng) => {
    const updatedPoints = [...points, { lat, lng }];
    const hullIndices = convexHull(updatedPoints.map(p => [p.lat, p.lng]));
    const hullPoints = hullIndices.map(index => updatedPoints[index]);
    setPoints(hullPoints);
  };

  const handleClick = (e) => {
    const { lat, lng } = e.latlng;
    addPoint(lat, lng);
    console.log('Clicked Point:', { latitude: lat, longitude: lng });
  };

  useMapEvents({
    click: handleClick
  });

  return null;
};

const PlantIcon = ({ x, y, name, index, onDelete }) => {
  const [image] = useImage(treeIconUrl);
  const [hover, setHover] = useState(false);

  const handleDelete = () => {
    onDelete(index);
  };

  return (
    <>
      <Image
        image={image}
        x={x}
        y={y}
        width={8}
        height={8}
        onMouseEnter={() => setHover(true)}
        onMouseLeave={() => setHover(false)}
        onClick={handleDelete}
      />
      {hover && (
        <Text
          text={`${name} (${index})`}
          x={x}
          y={y - 20}
          fontSize={14}
          fill="black"
          align="center"
        />
      )}
    </>
  );
};

const Geofencing = () => {
  const [latitude, setLatitude] = useState(11.0067);
  const [longitude, setLongitude] = useState(76.9614);
  const [points, setPoints] = useState([]);
  const [trees, setTrees] = useState([]);
  const [searchQuery, setSearchQuery] = useState('');
  const [drawing, setDrawing] = useState(false);
  const [selectedPlant, setSelectedPlant] = useState('');
  const [numberOfTrees, setNumberOfTrees] = useState(0);
  const [plantPositions, setPlantPositions] = useState([]);

  const tomtomApiKey = 'PB8zsg9D7IKuZE3OhxWi9XJoLOyWuOGw';

  const plantNames = {
    cotton: 'Cotton',
    mango: 'Mango',
    coconut: 'Coconut',
    banana: 'Banana',
    cardamom: 'Cardamom',
    tea: 'Tea'
  };

  const handlePlantSelection = (e) => {
    setSelectedPlant(e.target.value);
  };

  const handleNumberOfTreesChange = (e) => {
    setNumberOfTrees(parseInt(e.target.value, 10));
  };

  const addTree = (lat, lng) => {
    setTrees([...trees, { lat, lng }]);
  };

  const handleCanvasClick = (e) => {
    if (!selectedPlant) {
      console.warn('No plant selected');
      return;
    }
  
    const stage = e.target.getStage();
    const pointerPosition = stage.getPointerPosition();
    const { x, y } = pointerPosition;
  
    // Check if the click point is inside the polygon boundaries
    const isInsidePolygon = isPointInPolygon(convertXYToLatLng(x, y, 1920, 1080), points.map(p => [p.lat, p.lng]));
    if (!isInsidePolygon) {
      console.warn('Plant must be placed inside the polygon boundaries.');
      return;
    }
  
    setPlantPositions([...plantPositions, { x, y, type: selectedPlant, name: plantNames[selectedPlant] }]);
  };
  
  const handleSearch = async () => {
    try {
      const res = await axios.get(`https://api.tomtom.com/search/2/search/${encodeURIComponent(searchQuery)}.json?key=${tomtomApiKey}`);
      if (res.data && res.data.results && res.data.results.length > 0) {
        const { position } = res.data.results[0];
        const newLat = parseFloat(position.lat);
        const newLon = parseFloat(position.lon);
        setLatitude(newLat);
        setLongitude(newLon);
        setPoints([{ lat: newLat, lng: newLon }]);
      } else {
        console.error('No results found for:', searchQuery);
      }
    } catch (error) {
      console.error('Error fetching location data:', error);
    }
  };

  const handleMarkerDrag = (index, e) => {
    const { lat, lng } = e.target.getLatLng();
    const updatedPoints = [...points];
    updatedPoints[index] = { lat, lng };
    setPoints(updatedPoints);
  };

  const handleDeleteMarker = async (index) => {
    const updatedPoints = points.filter((_, idx) => idx !== index);
    setPoints(updatedPoints);
    await deletePointFromServer(index, points);
  };

  const handleTreeDrag = (index, e) => {
    const { lat, lng } = e.target.getLatLng();
    const updatedTrees = [...trees];
    updatedTrees[index] = { lat, lng };
    setTrees(updatedTrees);
  };

  const handleDeleteTree = async (index) => {
    const updatedTrees = trees.filter((_, idx) => idx !== index);
    setTrees(updatedTrees);
    await deleteTreeFromServer(index, trees);
  };

  const deletePointFromServer = async (index, points) => {
    const { latitude, longitude } = points[index];
    console.log('Point deleted successfully:');
  };

  const deleteTreeFromServer = async (index, trees) => {
    const { latitude, longitude } = trees[index];
    console.log('Tree deleted successfully:');
  };

  const handleInputChange = (e) => {
    setSearchQuery(e.target.value);
  };

  const handleSaveToCanvas = () => {
    if (points.length > 2) {
      setDrawing(true);
    } else {
      console.warn('Cannot save to canvas: Not enough points to form a polygon.');
    }
  };

  const convertLatLngToXY = (lat, lng, mapWidth, mapHeight) => {
    const minLat = Math.min(...points.map(p => p.lat));
    const maxLat = Math.max(...points.map(p => p.lat));
    const minLng = Math.min(...points.map(p => p.lng));
    const maxLng = Math.max(...points.map(p => p.lng));

    const latRange = maxLat - minLat;
    const lngRange = maxLng - minLng;
    const scalingFactor = 0.5;

    const x = ((lng - minLng) / lngRange) * mapWidth * scalingFactor + (mapWidth * (1 - scalingFactor)) / 2;
    const y = ((maxLat - lat) / latRange) * mapHeight * scalingFactor + (mapHeight * (1 - scalingFactor)) / 2;
  
    return { x, y };
  };

  const handleDeletePlant = (index) => {
    const updatedPlants = plantPositions.filter((_, idx) => idx !== index);
    setPlantPositions(updatedPlants);
  };

  const distributeTreesInPolygon = () => {
    if (points.length <= 2 || numberOfTrees <= 0) {
      console.warn('Insufficient points to form a polygon or number of trees is invalid.');
      return;
    }
  
    const polygonBounds = points.map(p => [p.lat, p.lng]);
    const minLat = Math.min(...polygonBounds.map(p => p[0]));
    const maxLat = Math.max(...polygonBounds.map(p => p[0]));
    const minLng = Math.min(...polygonBounds.map(p => p[1]));
    const maxLng = Math.max(...polygonBounds.map(p => p[1]));
  
    const latRange = maxLat - minLat;
    const lngRange = maxLng - minLng;
  
    const rows = Math.ceil(Math.sqrt(numberOfTrees));
    const cols = Math.ceil(numberOfTrees / rows);
    const latStep = latRange / rows;
    const lngStep = lngRange / cols;
  
    const trees = [];
    let treePlaced = false;
  
    for (let i = 0; i < rows; i++) {
      for (let j = 0; j < cols; j++) {
        const lat = minLat + i * latStep;
        const lng = minLng + j * lngStep;
  
        if (isPointInPolygon([lat, lng], polygonBounds)) {
          const { x, y } = convertLatLngToXY(lat, lng, 1920, 1080);
          trees.push({ x, y, type: selectedPlant, name: plantNames[selectedPlant] });
        }
  
        if (trees.length >= numberOfTrees) {
          treePlaced = true;
          break;
        }
      }
      if (treePlaced) break;
    }
  
    setPlantPositions([...plantPositions, ...trees]);
  };
  
  
  const isPointInPolygon = (point, polygon) => {
    const [lat, lng] = point;
    let isInside = false;
    
    for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
      const [latI, lngI] = polygon[i];
      const [latJ, lngJ] = polygon[j];
  
      if (((lngI > lng) !== (lngJ > lng)) &&
          (lat < (latJ - latI) * (lng - lngI) / (lngJ - lngI) + latI)) {
        isInside = !isInside;
      }
    }
    
    return isInside;
  };
  const convertXYToLatLng = (x, y, mapWidth, mapHeight) => {
    const minLat = Math.min(...points.map(p => p.lat));
    const maxLat = Math.max(...points.map(p => p.lat));
    const minLng = Math.min(...points.map(p => p.lng));
    const maxLng = Math.max(...points.map(p => p.lng));
  
    const latRange = maxLat - minLat;
    const lngRange = maxLng - minLng;
    const scalingFactor = 0.5;
  
    const lat = minLat + ((y - (mapHeight * (1 - scalingFactor)) / 2) / (mapHeight * scalingFactor)) * latRange;
    const lng = minLng + ((x - (mapWidth * (1 - scalingFactor)) / 2) / (mapWidth * scalingFactor)) * lngRange;
  
    return { lat, lng };
  };
  

  return (
    <div>
      <h1>Geofencing Application</h1>
      <div className='latlong'>
        <p>Latitude: {latitude}</p>
        <p>Longitude: {longitude}</p>
        <p>Points: {points.length}</p>
      </div>
      <div className="search-bar-container mb-3 d-flex">
        <input
          type="text"
          className="form-control me-2"
          placeholder="Search for a location..."
          value={searchQuery}
          onChange={handleInputChange}
        />
        <button className="btn btn-primary" onClick={handleSearch}>Search</button>
      </div>

      <MapContainer center={[latitude, longitude]} zoom={12} style={{ height: '500px', width: '100%' }} className='map'>
        <TileLayer url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png" />
        <MapClickHandler points={points} setPoints={setPoints} />

        {points.map((point, idx) => (
          <Marker
            key={idx}
            position={[point.lat, point.lng]}
            draggable={true}
            icon={customMarkerIcon}
            eventHandlers={{
              dragend: (e) => handleMarkerDrag(idx, e),
              click: () => handleDeleteMarker(idx)
            }}
          />
        ))}

        {points.length > 2 && <Polygon positions={points.map(p => [p.lat, p.lng])} pathOptions={{ fillColor: 'blue', fillOpacity: 0.4, color: 'blue' }} />}

        {trees.map((tree, idx) => (
          <Marker
            key={idx}
            position={[tree.lat, tree.lng]}
            draggable={true}
            icon={customMarkerIcon}
            eventHandlers={{
              dragend: (e) => handleTreeDrag(idx, e),
              click: () => handleDeleteTree(idx)
            }}
          />
        ))}
      </MapContainer>
      <div className="button-container">
          <button className="btn btn-primary" onClick={handleSaveToCanvas}>Save to Canvas</button>
      </div>
      <div className="canvas-container mt-4">
        {drawing && (
          <TransformWrapper
            initialScale={1}
            initialPositionX={200}
            initialPositionY={100}
          >
            {({ zoomIn, zoomOut, resetTransform, ...rest }) => (
              <>
                <div className="tools">
                  <button onClick={() => zoomIn()}>+</button>
                  <button onClick={() => zoomOut()}>-</button>
                  <button onClick={() => resetTransform()}>x</button>
                </div>
                <TransformComponent>
                  <Stage width={1920} height={1080} onClick={handleCanvasClick} pixelRatio={window.devicePixelRatio}>
                    <Layer>
                      <Shape
                        sceneFunc={(context, shape) => {
                          context.beginPath();
                          points.forEach((point, index) => {
                            const { x, y } = convertLatLngToXY(point.lat, point.lng, 1920, 1080);
                            if (index === 0) {
                              context.moveTo(x, y);
                            } else {
                              context.lineTo(x, y);
                            }
                          });
                          context.closePath();
                          context.fillStrokeShape(shape);
                        }}
                        fill="#00FF00"
                        stroke="#000000"
                        strokeWidth={1}
                      />

                      {plantPositions.map((pos, index) => (
                        <PlantIcon key={index} x={pos.x} y={pos.y} name={pos.name} index={index} onDelete={handleDeletePlant} />
                      ))}
                    </Layer>
                  </Stage>
                </TransformComponent>
              </>
            )}
          </TransformWrapper>
        )}
      </div>

      <div className="plant-selection mt-4">
        <h3>Plant Selection</h3>
        <select className="form-select" onChange={handlePlantSelection} value={selectedPlant}>
          <option value="">Select a plant...</option>
          {Object.keys(plantNames).map((plant, index) => (
            <option key={index} value={plant}>{plantNames[plant]}</option>
          ))}
        </select>
        <input
          type="number"
          className="form-control mt-2"
          placeholder="Number of trees..."
          value={numberOfTrees}
          onChange={handleNumberOfTreesChange}
        />
        <button className="btn btn-primary mt-2" onClick={distributeTreesInPolygon}>Print Trees</button>
      </div>

      <div className="plant-summary mt-4">
        <h3>Plant Summary</h3>
        <table className="table">
          <thead>
            <tr>
              <th scope="col">Name</th>
              <th scope="col">Count</th>
            </tr>
          </thead>
          <tbody>
            {Object.keys(plantNames).map((plant, index) => {
              const count = plantPositions.filter(pos => pos.type === plant).length;
              return (
                <tr key={index}>
                  <td>{plantNames[plant]}</td>
                  <td>{count}</td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default Geofencing;
