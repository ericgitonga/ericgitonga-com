#!/usr/bin/env python3
"""Desktop GUI Wrapper for Direct Media Upload (ericgitonga.com).

Provides a native desktop interface for uploading photographs to Angry Hosting
and refreshing the live Vercel Blob media manifest with zero Git/PR steps.

Usage:
    python scripts/upload_photo_gui.py
"""

from __future__ import annotations

import os
import pathlib
import sys
import threading
import tkinter as tk
from tkinter import filedialog, messagebox, ttk
import webbrowser

from PIL import Image, ImageTk

# Import upload primitives from the CLI engine
sys.path.insert(0, str(pathlib.Path(__file__).resolve().parent))
from upload_plate_photo import (
    CONFIG_FILE,
    VALID_PLATES,
    prompt_and_save_credentials,
    resolve_credentials,
    trigger_sync,
    upload_via_ftps,
)


class MediaUploaderApp(tk.Tk):
    def __init__(self):
        super().__init__()
        self.title("ericgitonga.com — Media Uploader")
        self.geometry("740x820")
        self.minsize(680, 720)

        self.selected_file: pathlib.Path | None = None
        self.preview_image: ImageTk.PhotoImage | None = None
        self.creds = resolve_credentials()

        self._configure_styles()
        self._build_ui()

    def _configure_styles(self):
        self.style = ttk.Style(self)
        self.style.theme_use("clam")

        # Color palette
        self.bg_color = "#F8FAFC"
        self.card_bg = "#FFFFFF"
        self.primary_color = "#1E3A8A"
        self.text_color = "#0F172A"
        self.muted_color = "#64748B"
        self.border_color = "#E2E8F0"
        self.accent_green = "#059669"

        self.configure(bg=self.bg_color)
        self.style.configure(".", background=self.bg_color, font=("Sans", 10))
        self.style.configure("TFrame", background=self.bg_color)
        self.style.configure("Card.TFrame", background=self.card_bg, relief="solid", borderwidth=1)
        self.style.configure("TLabel", background=self.bg_color, foreground=self.text_color)
        self.style.configure("Header.TLabel", font=("Sans", 16, "bold"), foreground=self.text_color)
        self.style.configure("Subheader.TLabel", font=("Sans", 10), foreground=self.muted_color)
        self.style.configure("Section.TLabel", font=("Sans", 11, "bold"), foreground=self.primary_color)
        self.style.configure("Primary.TButton", font=("Sans", 11, "bold"), background=self.primary_color, foreground="#FFFFFF")
        self.style.map("Primary.TButton", background=[("active", "#1D4ED8"), ("disabled", "#94A3B8")])

    def _build_ui(self):
        container = ttk.Frame(self, padding=24)
        container.pack(fill="both", expand=True)

        # Header Title
        title_frame = ttk.Frame(container)
        title_frame.pack(fill="x", pady=(0, 16))
        ttk.Label(title_frame, text="Media Gallery Uploader", style="Header.TLabel").pack(anchor="w")
        ttk.Label(
            title_frame,
            text="Direct edge intake for ericgitonga.com — upload once, live instantly without Git or PRs.",
            style="Subheader.TLabel",
        ).pack(anchor="w", pady=(2, 0))

        # Top Section: File Picker & Preview
        picker_frame = ttk.LabelFrame(container, text="  1. Select Photograph  ", padding=14)
        picker_frame.pack(fill="x", pady=(0, 14))

        picker_btn_frame = ttk.Frame(picker_frame)
        picker_btn_frame.pack(fill="x")
        self.btn_select = ttk.Button(picker_btn_frame, text="Choose File...", command=self._select_file)
        self.btn_select.pack(side="left")
        self.lbl_file_path = ttk.Label(picker_btn_frame, text="No image selected", foreground=self.muted_color)
        self.lbl_file_path.pack(side="left", padx=12, fill="x", expand=True)

        # Preview Container
        self.preview_frame = tk.Frame(picker_frame, bg="#F1F5F9", height=180, relief="solid", borderwidth=1)
        self.preview_frame.pack(fill="x", pady=(10, 0))
        self.preview_frame.pack_propagate(False)
        self.lbl_preview = tk.Label(self.preview_frame, text="Photograph preview will appear here", bg="#F1F5F9", fg=self.muted_color)
        self.lbl_preview.pack(expand=True)

        self.lbl_meta = ttk.Label(picker_frame, text="", style="Subheader.TLabel")
        self.lbl_meta.pack(anchor="w", pady=(6, 0))

        # Middle Section: Destination Metadata
        dest_frame = ttk.LabelFrame(container, text="  2. Gallery Destination  ", padding=14)
        dest_frame.pack(fill="x", pady=(0, 14))

        grid = ttk.Frame(dest_frame)
        grid.pack(fill="x")

        # Plate dropdown
        ttk.Label(grid, text="Plate:").grid(row=0, column=0, sticky="w", pady=4)
        self.var_plate = tk.StringVar(value="Drops")
        self.cb_plate = ttk.Combobox(grid, textvariable=self.var_plate, values=["Drops", "Daguerreotypes", "Daubs", "Dudus"], state="readonly", width=22)
        self.cb_plate.grid(row=0, column=1, sticky="w", padx=(8, 20), pady=4)
        self.cb_plate.bind("<<ComboboxSelected>>", self._on_plate_change)

        # Album text
        ttk.Label(grid, text="Album:").grid(row=0, column=2, sticky="w", pady=4)
        self.var_album = tk.StringVar(value="Drops")
        self.ent_album = ttk.Entry(grid, textvariable=self.var_album, width=24)
        self.ent_album.grid(row=0, column=3, sticky="w", padx=(8, 0), pady=4)

        # Remote Filename
        ttk.Label(grid, text="Filename:").grid(row=1, column=0, sticky="w", pady=8)
        self.var_filename = tk.StringVar(value="")
        self.ent_filename = ttk.Entry(grid, textvariable=self.var_filename, width=24)
        self.ent_filename.grid(row=1, column=1, sticky="w", padx=(8, 20), pady=8)

        # Sync Checkbox
        self.var_sync = tk.BooleanVar(value=True)
        self.chk_sync = ttk.Checkbutton(grid, text="Instant Live Sync (rebuild Vercel Blob manifest)", variable=self.var_sync)
        self.chk_sync.grid(row=1, column=2, columnspan=2, sticky="w", padx=(8, 0), pady=8)

        # Bottom Section: Action & Log
        action_frame = ttk.Frame(container)
        action_frame.pack(fill="both", expand=True)

        btn_row = ttk.Frame(action_frame)
        btn_row.pack(fill="x", pady=(0, 8))

        self.btn_upload = ttk.Button(btn_row, text="Upload Photo & Sync", style="Primary.TButton", command=self._start_upload)
        self.btn_upload.pack(side="left", padx=(0, 10))

        self.progress = ttk.Progressbar(btn_row, mode="indeterminate", length=220)

        # Console Log Area
        self.log_text = tk.Text(action_frame, height=9, bg="#0F172A", fg="#F8FAFC", font=("Monospace", 9), relief="solid", borderwidth=1, padx=8, pady=8)
        self.log_text.pack(fill="both", expand=True)
        self.log_text.tag_config("info", foreground="#94A3B8")
        self.log_text.tag_config("success", foreground="#34D399")
        self.log_text.tag_config("error", foreground="#F87171")
        self.log_text.tag_config("bold", foreground="#FFFFFF", font=("Monospace", 9, "bold"))

        # Footer link row
        self.footer_frame = ttk.Frame(container)
        self.footer_frame.pack(fill="x", pady=(8, 0))
        self.lbl_live_status = ttk.Label(self.footer_frame, text="", style="Subheader.TLabel")
        self.lbl_live_status.pack(side="left")

        self.log("Ready. Select an image file to begin.", "info")

    def log(self, message: str, tag: str = "info"):
        self.log_text.insert("end", f"• {message}\n", tag)
        self.log_text.see("end")

    def _select_file(self):
        filetypes = [
            ("Image files", "*.jpg *.jpeg *.png *.webp *.gif"),
            ("JPEG files", "*.jpg *.jpeg"),
            ("PNG files", "*.png"),
            ("All files", "*.*"),
        ]
        filename = filedialog.askopenfilename(title="Select Photograph", filetypes=filetypes)
        if not filename:
            return

        self.selected_file = pathlib.Path(filename)
        self.lbl_file_path.config(text=str(self.selected_file), foreground=self.text_color)
        self.var_filename.set(self.selected_file.name)

        # Render preview
        try:
            with Image.open(self.selected_file) as img:
                orig_w, orig_h = img.size
                file_kb = self.selected_file.stat().st_size / 1024.0

                # Fit to preview frame (height 170)
                thumb = img.copy()
                thumb.thumbnail((480, 170))
                self.preview_image = ImageTk.PhotoImage(thumb)
                self.lbl_preview.config(image=self.preview_image, text="")

                self.lbl_meta.config(
                    text=f"Resolution: {orig_w} × {orig_h} px  •  Size: {file_kb:.1f} KB  •  Format: {img.format}"
                )
        except Exception as e:
            self.lbl_preview.config(text=f"Cannot preview image: {e}")

        self.log(f"Selected: {self.selected_file.name}", "bold")

    def _on_plate_change(self, event=None):
        plate = self.var_plate.get()
        if plate == "Drops":
            self.var_album.set("Drops")
        elif plate == "Daguerreotypes":
            self.var_album.set("Beauty In The Ordinary")
        elif plate == "Daubs":
            self.var_album.set("Acid")
        elif plate == "Dudus":
            self.var_album.set("Ants")

    def _start_upload(self):
        if not self.selected_file or not self.selected_file.is_file():
            messagebox.showwarning("No File", "Please select a photograph first.")
            return

        plate = self.var_plate.get().strip()
        album = self.var_album.get().strip() or plate
        target_name = self.var_filename.get().strip() or self.selected_file.name

        # Validate credentials
        if not self.creds.get("FTP_PASS"):
            self.creds = prompt_and_save_credentials(self.creds)
            if not self.creds.get("FTP_PASS"):
                messagebox.showerror("Credentials Missing", "Angry Hosting FTPS credentials are required.")
                return

        self.btn_upload.config(state="disabled")
        self.btn_select.config(state="disabled")
        self.progress.pack(side="left", padx=10)
        self.progress.start(10)

        # Run in worker thread
        thread = threading.Thread(
            target=self._upload_worker,
            args=(self.selected_file, plate, album, target_name, self.var_sync.get()),
            daemon=True,
        )
        thread.start()

    def _upload_worker(self, path: pathlib.Path, plate: str, album: str, filename: str, do_sync: bool):
        try:
            self.log(f"Connecting to {self.creds['FTP_HOST']} over TLS 1.2+ FTPS...", "info")
            url = upload_via_ftps(path, plate, album, filename, self.creds)
            self.log(f"Upload complete! File size verified.", "success")
            self.log(f"Public URL: {url}", "bold")

            if do_sync:
                self.log("Triggering Vercel Blob media manifest rebuild...", "info")
                cron_secret = self.creds.get("CRON_SECRET", "")
                trigger_sync(cron_secret)
                self.log("Manifest synced and published to Vercel CDN!", "success")

            self.after(0, self._on_upload_success, url, plate)
        except Exception as err:
            self.log(f"Upload failed: {err}", "error")
            self.after(0, self._on_upload_failure, str(err))

    def _on_upload_success(self, live_url: str, plate: str):
        self.progress.stop()
        self.progress.pack_forget()
        self.btn_upload.config(state="normal")
        self.btn_select.config(state="normal")

        plate_slug = plate.lower()
        live_page = f"https://ericgitonga.com/{plate_slug}"
        self.lbl_live_status.config(
            text=f"Live at: {live_page}",
            foreground=self.accent_green,
        )

        resp = messagebox.askyesno(
            "Upload Complete",
            f"Photograph successfully uploaded and indexed!\n\nWould you like to open {live_page} in your browser?",
        )
        if resp:
            webbrowser.open(live_page)

    def _on_upload_failure(self, err_msg: str):
        self.progress.stop()
        self.progress.pack_forget()
        self.btn_upload.config(state="normal")
        self.btn_select.config(state="normal")
        messagebox.showerror("Upload Error", f"Failed to upload photo:\n\n{err_msg}")


def main():
    app = MediaUploaderApp()
    app.mainloop()


if __name__ == "__main__":
    main()
