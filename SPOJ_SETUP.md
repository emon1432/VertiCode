# SPOJ Cloudflare Bypass Setup

SPOJ uses aggressive Cloudflare protection that blocks automated requests. To enable SPOJ sync, you need to install **FlareSolverr**.

## Quick Setup (Recommended)

### 1. Install Docker (if not already installed)
```bash
# Ubuntu/Debian
sudo apt update
sudo apt install docker.io
sudo systemctl start docker
sudo systemctl enable docker
sudo usermod -aG docker $USER
# Log out and back in for group changes
```

### 2. Run FlareSolverr Container
```bash
docker run -d \
  --name=flaresolverr \
  -p 8191:8191 \
  -e LOG_LEVEL=info \
  --restart unless-stopped \
  ghcr.io/flaresolverr/flaresolverr:latest
```

### 3. Configure Laravel
Add to your `.env` file:
```env
FLARESOLVERR_URL=http://localhost:8191
```

### 4. Test It
```bash
php artisan test:spoj e_mon
```

You should now see SPOJ profiles loading successfully!

## How It Works

1. **FlareSolverr** runs as a background service
2. When SPOJ sync runs, it sends requests through FlareSolverr
3. FlareSolverr opens a real browser, solves the Cloudflare challenge
4. Returns the actual page content to your application
5. Takes **5-15 seconds** per request (Cloudflare challenge time)

## Verify FlareSolverr is Running

```bash
# Check if container is running
docker ps | grep flaresolverr

# Test the API
curl -X POST http://localhost:8191/v1 \
  -H "Content-Type: application/json" \
  -d '{"cmd":"request.get","url":"https://www.spoj.com"}'
```

## Troubleshooting

### Container Not Starting
```bash
# Check logs
docker logs flaresolverr

# Restart container
docker restart flaresolverr
```

### Port Already in Use
```bash
# Use a different port
docker run -d --name=flaresolverr -p 8192:8191 ghcr.io/flaresolverr/flaresolverr:latest

# Update .env
FLARESOLVERR_URL=http://localhost:8192
```

### Still Getting Errors
```bash
# Remove old container and recreate
docker rm -f flaresolverr
docker run -d --name=flaresolverr -p 8191:8191 ghcr.io/flaresolverr/flaresolverr:latest
```

## Without FlareSolverr

If you don't install FlareSolverr:
- ✅ Other platforms (Codeforces, LeetCode, etc.) work normally
- ⚠️ SPOJ sync will fail gracefully with clear error messages
- ℹ️ System remains stable - no crashes or hangs

## Production Deployment

For production servers, ensure FlareSolverr starts automatically:

```bash
# Already included with --restart unless-stopped flag
docker run -d \
  --name=flaresolverr \
  -p 8191:8191 \
  -e LOG_LEVEL=warn \
  --restart unless-stopped \
  ghcr.io/flaresolverr/flaresolverr:latest
```

## Resource Usage

- **CPU**: Low (~1-5% during requests)
- **Memory**: ~200MB
- **Disk**: ~500MB (Chrome + dependencies)
- **Network**: Only when SPOJ requests are made

## Alternative: Manual SPOJ Entry

If you don't want to run FlareSolverr, users can manually enter their SPOJ stats in the UI (future feature).
