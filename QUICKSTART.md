# Quick Start Guide

## 1. Install Dependencies

```bash
composer install
```

## 2. Setup Environment

Copy the environment template and configure:

```bash
# Create .env file (see ENV_SETUP.md for template)
nano .env
```

## 3. Setup Database

```bash
# Create database
createdb tikiti_organizer

# Run migrations
psql -U postgres -d tikiti_organizer -f database/migrations/001_create_examples_table.sql
```

## 4. Start Development Server

```bash
php -S localhost:8000 -t public
```

## 5. Test the API

```bash
# Health check
curl http://localhost:8000/health

# Example endpoint
curl http://localhost:8000/api/v1/example
```

## Angular Integration Example

### Service (TypeScript)

```typescript
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import * as CryptoJS from 'crypto-js';

@Injectable({
  providedIn: 'root'
})
export class ApiService {
  private apiUrl = 'http://localhost:8000/api/v1';
  private encryptionKey = 'your_32_character_key'; // Must match server key

  constructor(private http: HttpClient) {}

  private decrypt(data: string): any {
    const bytes = CryptoJS.AES.decrypt(data, this.encryptionKey);
    return JSON.parse(bytes.toString(CryptoJS.enc.Utf8));
  }

  get(endpoint: string): Observable<any> {
    return this.http.get(`${this.apiUrl}${endpoint}`).pipe(
      map((response: any) => {
        if (response.success && response.data) {
          return this.decrypt(response.data);
        }
        throw new Error('Invalid response');
      })
    );
  }

  post(endpoint: string, data: any): Observable<any> {
    return this.http.post(`${this.apiUrl}${endpoint}`, data).pipe(
      map((response: any) => {
        if (response.success && response.data) {
          return this.decrypt(response.data);
        }
        throw new Error('Invalid response');
      })
    );
  }
}
```

### Component Usage

```typescript
import { Component, OnInit } from '@angular/core';
import { ApiService } from './api.service';

@Component({
  selector: 'app-example',
  template: '<div>{{ data | json }}</div>'
})
export class ExampleComponent implements OnInit {
  data: any;

  constructor(private apiService: ApiService) {}

  ngOnInit() {
    this.apiService.get('/example').subscribe(
      data => this.data = data,
      error => console.error(error)
    );
  }
}
```

## Notes

- All responses are encrypted - you must decrypt them in Angular
- Use `crypto-js` npm package for decryption: `npm install crypto-js @types/crypto-js`
- The encryption key must match between server and client
- CORS is configured for Angular development servers
