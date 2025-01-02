const express = require('express');
const bodyParser = require('body-parser');
const fs = require('fs');
const cors = require('cors');

const app = express();
const port = 3000;

const corsOptions = {
    origin: 'http://localhost:8080',
    optionsSuccessStatus: 200
};

app.use(cors(corsOptions));
app.use(bodyParser.json());
app.use(express.static('public'));

app.get('/html/addBerita.html', (req, res) => {
    res.sendFile(__dirname + '/public/html/addBerita.html');
});

app.get('/html/editBerita.html', (req, res) => {
    res.sendFile(__dirname + '/public/html/editBerita.html');
});

app.get('/getBerita', (req, res) => {
    const beritaId = parseInt(req.query.id);
    fs.readFile('data/berita.json', 'utf8', (err, data) => {
        if (err) {
            console.error('Error reading data:', err);
            res.status(500).send('Error reading data');
            return;
        }
        let beritaArray = [];
        if (data) {
            try { 
                beritaArray = JSON.parse(data); 
            } catch (parseError) { 
                console.error('Error parsing data:', parseError); 
                res.status(500).send('Error parsing data'); 
                return; 
            }
        }

        const berita = beritaArray.find(item => item.id === beritaId);
        if (berita) {
            res.send(berita);
        } else {
            res.status(404).send('Berita not found');
        }
    });
});

app.post('/updateBerita', (req, res) => {
    const updatedData = req.body;
    fs.readFile('data/berita.json', 'utf8', (err, data) => {
        if (err) {
            console.error('Error reading data:', err);
            res.status(500).send('Error reading data');
            return;
        }
        let beritaArray = [];
        if (data) {
            try { 
                beritaArray = JSON.parse(data); 
            } catch (parseError) { 
                console.error('Error parsing data:', parseError); 
                res.status(500).send('Error parsing data'); 
                return; 
            }
        }

        const index = beritaArray.findIndex(berita => berita.id === updatedData.id);
        if (index !== -1) {
            beritaArray[index] = updatedData;
        } else {
            beritaArray.push(updatedData);
        }

        fs.writeFile('data/berita.json', JSON.stringify(beritaArray, null, 2), 'utf8', (err) => {
            if (err) {
                console.error('Error saving data:', err);
                res.status(500).send('Error saving data');
            } else {
                console.log('Data successfully saved');
                res.send('Data successfully updated');
            }
        });
    });
});

app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}/`);
});