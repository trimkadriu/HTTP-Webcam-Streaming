using AForge.Video;
using AForge.Video.DirectShow;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.IO;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;

namespace WebCamStream
{
    public partial class MainForm : Form
    {
        FilterInfoCollection videoDevices;
        VideoCaptureDevice videoSource;
        Timer aliveTimer;
        Timer streamTimer;

        public MainForm()
        {
            InitializeComponent();
        }

        private void MainForm_Load(object sender, EventArgs e)
        {
            // Setup camera device
            videoDevices = new FilterInfoCollection(FilterCategory.VideoInputDevice);
            videoSource = new VideoCaptureDevice();
            videoSource = new VideoCaptureDevice(videoDevices[0].MonikerString);
            videoSource.NewFrame += videoSource_NewFrame;
            // TODO: FIX setting a lower resolution for faster and better streaming
            //videoSource.VideoResolution = videoSource.VideoCapabilities[4];

            // Setup alive communication
            aliveTimer = new Timer();
            aliveTimer.Interval = Config.ALIVE_TICK; // specify alive interval time as you want
            aliveTimer.Tick += new EventHandler(aliveTimer_Tick);
            aliveTimer.Start();

            // Setup cam stream
            streamTimer = new Timer();
            streamTimer.Interval = Config.DEFAULT_STREAM_SPEED;
            streamTimer.Tick += new EventHandler(streamTimer_Tick);
        }

        void videoSource_NewFrame(object sender, NewFrameEventArgs eventArgs)
        {
            pictureBoxOutput.Image = (Bitmap)eventArgs.Frame.Clone();
        }

        void aliveTimer_Tick(object sender, EventArgs e)
        {
            // Generate post objects
            Dictionary<string, object> postParameters = new Dictionary<string, object>();
            postParameters.Add("command", "alive");
            postParameters.Add("auth_id", "trimauthid");

            // Create request and receive response
            string postURL = Helper.getUrl("communicator");
            HttpWebResponse webResponse = FormUpload.MultipartFormDataPost(postURL, Config.userAgent, postParameters);

            // Process response
            StreamReader responseReader = new StreamReader(webResponse.GetResponseStream());
            string fullResponse = responseReader.ReadToEnd();
            webResponse.Close();

            dynamic jsonResponse = JsonConvert.DeserializeObject(fullResponse);
            string command = jsonResponse["command"];
            
            if ("start".Equals(command))
            {
                videoSource.Start();
                string streamSpeed = jsonResponse["stream_speed"];
                streamTimer.Interval = Int32.Parse(streamSpeed);
                streamTimer.Start();
            }
            else if("stop".Equals(command))
            {
                videoSource.Stop();
                streamTimer.Stop();
            }
        }

        void streamTimer_Tick(object sender, EventArgs e)
        {
            byte[] postData = Helper.convertImageToByteArray(pictureBoxOutput.Image);

            // Generate post objects
            Dictionary<string, object> postParameters = new Dictionary<string, object>();
            postParameters.Add("auth_id", "trimauthid");
            postParameters.Add("image", new FormUpload.FileParameter(postData, "image.jpg", "image/jpeg"));

            // Create request and receive response
            string postURL = Helper.getUrl("send");
            HttpWebResponse webResponse = FormUpload.MultipartFormDataPost(postURL, Config.userAgent, postParameters);

            // Process response
            StreamReader responseReader = new StreamReader(webResponse.GetResponseStream());
            string fullResponse = responseReader.ReadToEnd();
            webResponse.Close();
        }

        private void MainForm_FormClosing(object sender, FormClosingEventArgs e)
        {
            if (videoSource.IsRunning)
            {
                videoSource.Stop();
            }
            aliveTimer.Stop();
            streamTimer.Stop();
        }

        private void notifyIcon1_MouseDoubleClick(object sender, MouseEventArgs e)
        {
            this.Show();
            this.WindowState = FormWindowState.Normal;
        }

        private void MainForm_Resize(object sender, EventArgs e)
        {
            notifyIcon1.BalloonTipTitle = "WebCam Stream is on System Tray";
            notifyIcon1.BalloonTipText = "WebCam Stream is still running here. Click the icon on system tray to open it again.";

            if (FormWindowState.Minimized == this.WindowState)
            {
                notifyIcon1.Visible = true;
                notifyIcon1.ShowBalloonTip(100);
                this.Hide();
            }
            else if (FormWindowState.Normal == this.WindowState)
            {
                notifyIcon1.Visible = false;
            }
        }
    }
}
