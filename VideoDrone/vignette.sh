#!/bin/bash
#ffmpeg -i $1 -vcodec libx264 -maxrate 8000k -bufsize 1000K -minrate 10k -crf 24 -preset slow -ab 192k $1-reencodee.mp4
ffmpeg -y -i $1 -movflags faststart -c:v libx264 -preset slow -crf 22 -pix_fmt yuv420p -c:a libvo_aacenc -b:a 128k $1.optimize.mp4
