# Pusher Setup Guide for Real-Time Print Station

This guide will help you set up Pusher for the real-time print station functionality in your IT Portal application.

## 1. Create a Pusher Account

1. Go to [pusher.com](https://pusher.com) and create a free account
2. Create a new app in your Pusher dashboard
3. Choose the "Channels" product
4. Select your preferred cluster (e.g., `us-east-1`, `eu-west-1`, `ap-southeast-1`)

## 2. Get Your Pusher Credentials

From your Pusher app dashboard, note down:
- **App ID**
- **Key** 
- **Secret**
- **Cluster**

## 3. Configure Environment Variables

Add the following to your `.env` file:

```env
# Change from 'log' to 'pusher' to enable real-time broadcasting
BROADCAST_CONNECTION=pusher

# Pusher Configuration
PUSHER_APP_ID=your-pusher-app-id
PUSHER_APP_KEY=your-pusher-app-key
PUSHER_APP_SECRET=your-pusher-app-secret
PUSHER_APP_CLUSTER=your-cluster-name

# Frontend configuration
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

Replace the placeholder values with your actual Pusher credentials.

## 4. Test the Configuration

1. Start your Laravel development server:
   ```bash
   php artisan serve
   ```

2. Start the Vite development server:
   ```bash
   npm run dev
   ```

3. Open two browser windows:
   - Window 1: Navigate to `/print-available` (Print Station)
   - Window 2: Navigate to `/assets` (Assets page)

4. From the assets page, click "Send to Print Machine" on any asset
5. You should see the print job appear in real-time on the print station page

## 5. Print Station Usage

### Setting up the Print Station PC

1. On the dedicated print station PC, navigate to `/print-available`
2. The page will show:
   - Connection status (Connected/Disconnected)
   - Pending print jobs queue
   - Processing print jobs queue
   - Statistics (completed/failed jobs)

### Print Job Workflow

1. **From Mobile/Desktop**: Create an asset and click "Send to Print Machine"
2. **Print Station**: Job appears in "Pending Queue" in real-time
3. **Print Station**: Click "Play" button to start processing the job
4. **Print Station**: Job moves to "Processing Queue"
5. **Print Station**: Click "Check" to mark as completed or "X" to mark as failed

### JSPrintManager Integration

The print station integrates with JSPrintManager for actual label printing:

1. Download and install [JSPrintManager](https://neodynamic.com/downloads/jspm) on the print station PC
2. Start the JSPrintManager service
3. Connect your label printer (Zebra ZPL compatible recommended)
4. The print station will automatically detect available printers

## 6. Features

### Real-Time Updates
- Print jobs appear instantly on the print station when created
- Status updates are broadcast in real-time
- Connection status monitoring

### Print Job Management
- Priority-based queue ordering (urgent → high → normal → low)
- Job status tracking (pending → processing → completed/failed)
- Error handling and retry capabilities
- Print job history and statistics

### Multi-Language Support
- Full English/Arabic translation support
- RTL layout support for Arabic

### Security
- Authentication required for all print operations
- Company-based access control
- Print job authorization checks

## 7. Troubleshooting

### Connection Issues
- Check your Pusher credentials in `.env`
- Verify your cluster setting matches your Pusher app
- Check browser console for WebSocket errors

### Print Jobs Not Appearing
- Ensure `BROADCAST_CONNECTION=pusher` in `.env`
- Check Laravel logs for broadcasting errors
- Verify user has access to the company/assets

### JSPrintManager Issues
- Ensure JSPrintManager service is running
- Check printer connections and drivers
- Verify browser allows local connections to JSPrintManager

## 8. Production Deployment

For production deployment:

1. Set up SSL/HTTPS (required for WebSocket connections)
2. Configure Pusher for production environment
3. Set up proper firewall rules for JSPrintManager
4. Consider using a dedicated print station PC/tablet
5. Test thoroughly with your actual label printers

## 9. API Endpoints

The following API endpoints are available for print job management:

- `POST /api/print-jobs` - Create a new print job
- `GET /api/print-jobs/pending` - Get pending print jobs
- `GET /api/print-jobs/history` - Get print job history
- `GET /api/print-jobs/statistics` - Get print statistics
- `PATCH /api/print-jobs/{id}/status` - Update print job status
- `DELETE /api/print-jobs/{id}` - Cancel a print job

## 10. Customization

You can customize the print station by:

- Modifying `resources/js/pages/PrintStation/Index.vue`
- Updating print job priorities and statuses in `app/Models/PrintJob.php`
- Customizing ZPL label templates in the print functions
- Adding custom print job metadata and tracking

---

For support or questions, please refer to the Laravel and Pusher documentation or contact your system administrator. 