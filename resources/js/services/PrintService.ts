import type { Asset } from '@/types';

// Type declarations for JSPrintManager
declare global {
  interface Window {
    JSPM: {
      JSPrintManager: {
        auto_reconnect: boolean;
        start: () => void;
        websocket_status: number;
        WS: {
          onStatusChanged: () => void;
        };
        getPrinters: () => Promise<string[]>;
      };
      WSStatus: {
        Open: number;
        Closed: number;
        Blocked: number;
      };
      ClientPrintJob: new () => {
        clientPrinter: any;
        printerCommands: string;
        sendToClient: () => void;
      };
      DefaultPrinter: new () => any;
      InstalledPrinter: new (printerName: string) => any;
    };
  }
}

interface PrinterStatus {
  status: string;
  availablePrinters: string[];
  isConnected: boolean;
}

export class PrintService {
  private static instance: PrintService;
  private statusCallbacks: ((status: PrinterStatus) => void)[] = [];

  static getInstance(): PrintService {
    if (!PrintService.instance) {
      PrintService.instance = new PrintService();
    }
    return PrintService.instance;
  }

  // Function to dynamically load JavaScript files
  private loadScript(src: string): Promise<void> {
    return new Promise((resolve, reject) => {
      // Check if script is already loaded
      const existingScript = document.querySelector(`script[src="${src}"]`);
      if (existingScript) {
        resolve();
        return;
      }

      const script = document.createElement('script');
      script.src = src;
      script.onload = () => resolve();
      script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
      document.head.appendChild(script);
    });
  }

  // Load all required scripts
  private async loadRequiredScripts(): Promise<void> {
    try {
      this.notifyStatusChange('Loading print libraries...');
      
      // Load scripts in sequence
      await this.loadScript('/js/bluebird.min.js');
      await this.loadScript('/js/jquery-3.2.1.slim.min.js');
      await this.loadScript('/js/jsmanager/JSPrintManager.js');
      
      this.notifyStatusChange('Print libraries loaded successfully');
    } catch (error) {
      console.error('Failed to load scripts:', error);
      this.notifyStatusChange('Failed to load print libraries');
      throw error;
    }
  }

  // Initialize JSPrintManager
  async initializeJSPrintManager(): Promise<void> {
    // Load scripts if not already loaded
    if (typeof window.JSPM === 'undefined') {
      await this.loadRequiredScripts();
      // Add a small delay after loading scripts to ensure they're fully initialized
      await new Promise(resolve => setTimeout(resolve, 500));
    }

    return new Promise((resolve, reject) => {
      if (typeof window.JSPM !== 'undefined') {
        // WebSocket settings
        window.JSPM.JSPrintManager.auto_reconnect = true;
        window.JSPM.JSPrintManager.start();
        
        // Set up timeout for connection
        const connectionTimeout = setTimeout(() => {
          this.notifyStatusChange('JSPrintManager connection timeout', [], false);
          reject(new Error('JSPrintManager connection timeout'));
        }, 10000); // 10 second timeout
        
        window.JSPM.JSPrintManager.WS.onStatusChanged = () => {
          if (this.jspmWSStatus()) {
            clearTimeout(connectionTimeout);
            this.notifyStatusChange('Connected to JSPrintManager', [], true);
            
            // Add a small delay before getting printers to ensure connection is stable
            setTimeout(() => {
              // Get client installed printers
              window.JSPM.JSPrintManager.getPrinters().then((myPrinters: string[]) => {
                console.log('PrintService: Retrieved printers:', myPrinters);
                this.notifyStatusChange('Connected to JSPrintManager', myPrinters, true);
                resolve();
              }).catch((error) => {
                console.error('PrintService: Failed to get printers:', error);
                this.notifyStatusChange('Failed to get printers', [], false);
                reject(error);
              });
            }, 1000); // 1 second delay before getting printers
            
          } else {
            clearTimeout(connectionTimeout);
            this.notifyStatusChange('JSPrintManager connection failed', [], false);
            reject(new Error('JSPrintManager connection failed'));
          }
        };
      } else {
        const error = 'JSPrintManager not loaded';
        this.notifyStatusChange(error, [], false);
        reject(new Error(error));
      }
    });
  }

  // Check JSPrintManager WebSocket status
  private jspmWSStatus(): boolean {
    if (window.JSPM.JSPrintManager.websocket_status == window.JSPM.WSStatus.Open) {
      return true;
    } else if (window.JSPM.JSPrintManager.websocket_status == window.JSPM.WSStatus.Closed) {
      this.notifyStatusChange('JSPrintManager is not installed or not running! Download JSPM Client App from https://neodynamic.com/downloads/jspm', [], false);
      return false;
    } else if (window.JSPM.JSPrintManager.websocket_status == window.JSPM.WSStatus.Blocked) {
      this.notifyStatusChange('JSPM has blocked this website!', [], false);
      return false;
    }
    return false;
  }

  // Get available printers
  async getAvailablePrinters(): Promise<string[]> {
    if (typeof window.JSPM !== 'undefined' && this.jspmWSStatus()) {
      return await window.JSPM.JSPrintManager.getPrinters();
    }
    return [];
  }

  // Generate ZPL code for asset label (EXACT same as Assets/Index.vue)
  private generateAssetLabelZPL(asset: Asset): string {
    // Set content to print - Create Zebra ZPL commands for 2" x 1" label (406 x 203 dots at 203 DPI)
    let cmds = "^XA";
    
    // Set label dimensions for 2" x 1" at 203 DPI
    cmds += "^PW406";  // Print width 406 dots (2 inches * 203 DPI)
    cmds += "^LL203";  // Label length 203 dots (1 inch * 203 DPI)
    
    // Set print density for darker printing
    cmds += "^MD30";   // Media darkness (0-30, higher = darker)
    cmds += "^SD15";   // Set darkness (0-30, higher = darker)
    
    // Company logo on the left side (centered with top margin)
    cmds += "^FO10,50^GFA,329,329,7,,,,,,K0F1C,J01E3E,I039F1C4,I079E10F,I07BF03E,I073E17E,00213E7FC,00607CFF8,00F87CFF,00F8F9F800C,00FCF8J08,007EF,003E6,03BEK03F,039FK07F,039FK07F,07CFK03F8,008F,I0F8I018,I07J03C,03DK03E68,07FCJ01C7,03FFJ01E7,03FF8I01F3,017FEI09F,I0FE0018F8,0043E003CF8,00F1I03C7C,00F80807C3C,0070700F838,0020F00F008,I03E00F3,I07E01F38,I07C01E78,I03801E3,L01E,L01C,L01,,,,,^FS";
    
    // Company name above asset tag (small text)
    cmds += "^FO70,30^A0N,18,18^FDFAHAD NAWAF ALZEER HOLDING CO.^FS";  // Company name in English
    
    // Asset tag next to logo (with double margin under company name)
    cmds += `^FO70,65^A0N,24,24^FD${asset.asset_tag}^FS`;
    
    // Serial number or asset tag as barcode (Code 128) - centered
    cmds += "^FO70,100^BY2,2,35";  // Adjusted position to maintain spacing
    cmds += "^BCN,35,Y,N,N";
    cmds += `^FD${asset.serial_number || asset.asset_tag}^FS`;
    
    // QR Code on the right side - centered with top margin
    cmds += "^FO320,50^BQN,2,4";  // Moved down to align with logo and title
    cmds += `^FDMM,${asset.asset_tag}^FS`;
    
    // Date at bottom left (below barcode)
    cmds += `^FO70,175^A0N,18,18^FD${new Date().toISOString().split('T')[0]}^FS`;
    
    cmds += "^XZ";
    
    return cmds;
  }

  // Print single asset label
  async printAssetLabel(asset: Asset, useDefaultPrinter: boolean = false, selectedPrinter: string = ''): Promise<void> {
    if (!this.jspmWSStatus()) {
      throw new Error('JSPrintManager is not connected');
    }

    if (!useDefaultPrinter && !selectedPrinter) {
      throw new Error('No printer selected');
    }

    try {
      // Create a ClientPrintJob
      const cpj = new window.JSPM.ClientPrintJob();
      
      // Set Printer type
      if (useDefaultPrinter) {
        cpj.clientPrinter = new window.JSPM.DefaultPrinter();
      } else {
        cpj.clientPrinter = new window.JSPM.InstalledPrinter(selectedPrinter);
      }
      
      // Generate ZPL commands
      const zplCode = this.generateAssetLabelZPL(asset);
      cpj.printerCommands = zplCode;
      
      // Send print job to printer!
      cpj.sendToClient();
      
      console.log(`Printed label for asset ${asset.asset_tag}`);
    } catch (error) {
      console.error('Print error:', error);
      throw error;
    }
  }

  // Print multiple asset labels
  async printMultipleAssetLabels(assets: Asset[], useDefaultPrinter: boolean = false, selectedPrinter: string = ''): Promise<void> {
    if (!this.jspmWSStatus()) {
      throw new Error('JSPrintManager is not connected');
    }

    if (!useDefaultPrinter && !selectedPrinter) {
      throw new Error('No printer selected');
    }

    try {
      // Create a ClientPrintJob
      const cpj = new window.JSPM.ClientPrintJob();
      
      // Set Printer type
      if (useDefaultPrinter) {
        cpj.clientPrinter = new window.JSPM.DefaultPrinter();
      } else {
        cpj.clientPrinter = new window.JSPM.InstalledPrinter(selectedPrinter);
      }
      
      // Generate ZPL commands for all selected assets
      let allCommands = "";
      
      assets.forEach((asset) => {
        allCommands += this.generateAssetLabelZPL(asset);
      });
      
      cpj.printerCommands = allCommands;
      
      // Send print job to printer!
      cpj.sendToClient();
      
      console.log(`Printed ${assets.length} labels`);
    } catch (error) {
      console.error('Print error:', error);
      throw error;
    }
  }

  // Subscribe to status changes
  onStatusChange(callback: (status: PrinterStatus) => void): void {
    this.statusCallbacks.push(callback);
  }

  // Unsubscribe from status changes
  offStatusChange(callback: (status: PrinterStatus) => void): void {
    const index = this.statusCallbacks.indexOf(callback);
    if (index > -1) {
      this.statusCallbacks.splice(index, 1);
    }
  }

  // Notify all subscribers of status changes
  private notifyStatusChange(status: string, availablePrinters: string[] = [], isConnected: boolean = false): void {
    this.statusCallbacks.forEach(callback => {
      callback({
        status,
        availablePrinters,
        isConnected
      });
    });
  }
} 