/*
 Copyright 2016 Google Inc. All Rights Reserved.
 Licensed under the Apache License, Version 2.0 (the "License");
 you may not use this file except in compliance with the License.
 You may obtain a copy of the License at
     http://www.apache.org/licenses/LICENSE-2.0
 Unless required by applicable law or agreed to in writing, software
 distributed under the License is distributed on an "AS IS" BASIS,
 WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 See the License for the specific language governing permissions and
 limitations under the License.
*/

// Names of the two caches used in this version of the service worker.
// Change to v2, etc. when you update any of the local resources, which will
// in turn trigger the install event again.
const CACHE = 'cache-v1';

// A list of local resources we always want to be cached.
const PRECACHE_URLS = [
  'index.html',
  './', // Alias for index.html
  'index.min.css',
  'at-grid-min.css',
  'localforage.min.js',
  'index.js'
];

// The install handler takes care of precaching the resources we always need.
self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(precache(PRECACHE_URLS));
});

// The activate handler takes care of cleaning up old caches.
self.addEventListener('activate', event => {
  const currentCaches = [CACHE];
  caches.keys().then(cacheNames => {
    return cacheNames.filter(cacheName => !currentCaches.includes(cacheName));
  }).then(cachesToDelete => {
    return Promise.all(cachesToDelete.map(cacheToDelete => {
      return caches.delete(cacheToDelete);
    }));
  }).then(() => self.clients.claim())
});

// The fetch handler serves responses for same-origin resources from a cache.
// If no response is found, it populates the runtime cache with the response
// from the network before returning it to the page.
self.addEventListener('fetch', event => {
  // Skip cross-origin requests, like those for Google Analytics.
  if (event.request.url.startsWith(self.location.origin)) {
    var request = event.request;
    if (request.method === 'GET') {
      if (request.url == 'sw.js'){
        console.log('sw from network');
        event.respondWith(fromNetwork(request, 1000));
        return;
      }
      event.respondWith(fromNetwork(request, 1000).catch(function () {
        return fromCache(request);
      }));
    }
  }
});

function precache(urls) {
  return caches.open(CACHE).then(function (cache) {
    return cache.addAll(urls);
  });
}

function fromCache(request) {
  return caches.open(CACHE).then(function (cache) {
    return cache.match(request).then(function (matching) {
      return matching || Promise.reject('no-match');
    });
  });
}

function fromNetwork(request, timeout) {
  return new Promise(function (fulfill, reject) {
    var timeoutId = setTimeout(reject, timeout);
    fetch(request).then(function (response) {
      var rsp = response.clone();
      clearTimeout(timeoutId);
      fulfill(response);
      caches.open(CACHE).then(function (cache) {
        cache.put(request, rsp);
      });
    }, reject);
  });
}