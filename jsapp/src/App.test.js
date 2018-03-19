import React from 'react';
import ReactDOM from 'react-dom';
import App from './App';
import Display from './App';
import TitleList from './App';
import PreviewPane from './App';
import fetchMock from 'fetch-mock';

fetchMock.get('end:/legacy-sitemap/api/sitemap', [{"url":"d\/content\/unix-text-file-database.html","title":"Unix Text File Database "},{"url":"d\/content\/file-sync-and-share-and-file-server-terminology-clashes.html","title":"File Sync and Share and File Server Terminology Clashes "}]);
fetchMock.post('end:/legacy-sitemap/api/move', {});

it('renders without crashing', () => {
  const div = document.createElement('div');
  ReactDOM.render(<App />, div);
  ReactDOM.unmountComponentAtNode(div);
});

// Display
it('renders without crashing', () => {
  const div = document.createElement('div');
  ReactDOM.render(<Display />, div);
  ReactDOM.unmountComponentAtNode(div);
});

// TitleList
it('renders without crashing', () => {
  const div = document.createElement('div');
  ReactDOM.render(<TitleList list={[]} />, div);
  ReactDOM.unmountComponentAtNode(div);
});

// PreviewPane
it('renders without crashing', () => {
  const div = document.createElement('div');
  ReactDOM.render(<PreviewPane url="http://example.com" />, div);
  ReactDOM.unmountComponentAtNode(div);
});


