#!/usr/bin/env python3
"""Streamlit Web GUI for Direct Media Upload (ericgitonga.com).

Run with:
    conda run -n ds streamlit run scripts/upload_gui_web.py
"""

from __future__ import annotations

import pathlib
import sys
import tempfile
import streamlit as st
from PIL import Image

# Import upload primitives from the CLI engine
sys.path.insert(0, str(pathlib.Path(__file__).resolve().parent))
from upload_plate_photo import (
    VALID_PLATES,
    prompt_and_save_credentials,
    resolve_credentials,
    trigger_sync,
    upload_via_ftps,
)

st.set_page_config(
    page_title="ericgitonga.com — Media Uploader",
    page_icon="📷",
    layout="centered",
)

st.title("📷 Gallery Media Uploader")
st.caption("Direct intake for `ericgitonga.com` — upload photos to Angry Hosting & trigger live Vercel Blob sync without Git, PRs, or deploys.")

# Credentials check
creds = resolve_credentials()
with st.sidebar:
    st.header("⚙️ Server Configuration")
    st.text(f"Host: {creds.get('FTP_HOST') or 'angryhosting.com'}")
    st.text(f"User: {creds.get('FTP_USER') or '(not set)'}")
    st.text(f"Sync Secret: {'Configured ✅' if creds.get('CRON_SECRET') else 'Missing ⚠️'}")

    if not creds.get("FTP_PASS"):
        st.warning("FTP Password is not configured in local environment.")
        new_pass = st.text_input("Enter FTPS Password:", type="password")
        if new_pass:
            creds["FTP_PASS"] = new_pass

# Step 1: File selection
st.subheader("1. Select Photograph")
uploaded_file = st.file_uploader(
    "Choose a photo to upload",
    type=["jpg", "jpeg", "png", "webp", "gif"],
    help="Supports high-resolution photographs. EXIF and resolution are preserved.",
)

if uploaded_file:
    col1, col2 = st.columns([1, 1])
    with col1:
        img = Image.open(uploaded_file)
        st.image(img, caption=f"{uploaded_file.name} ({img.size[0]} × {img.size[1]} px)", use_container_width=True)
    with col2:
        st.write(f"**Filename:** `{uploaded_file.name}`")
        st.write(f"**Size:** `{uploaded_file.size / 1024.0:.1f} KB`")
        st.write(f"**Format:** `{img.format}`")

    st.markdown("---")
    st.subheader("2. Gallery Destination")

    col_plate, col_album = st.columns(2)
    with col_plate:
        plate_selection = st.selectbox(
            "Target Plate",
            options=["Drops", "Daguerreotypes", "Daubs", "Dudus"],
            index=0,
        )
    with col_album:
        default_album = "Drops" if plate_selection == "Drops" else ("Beauty In The Ordinary" if plate_selection == "Daguerreotypes" else plate_selection)
        album_name = st.text_input("Album Name", value=default_album)

    remote_filename = st.text_input("Remote Filename", value=uploaded_file.name)
    do_sync = st.checkbox("Rebuild live Vercel Blob manifest immediately", value=True)

    if st.button("🚀 Upload & Sync to Live Site", type="primary", use_container_width=True):
        if not creds.get("FTP_PASS"):
            st.error("Cannot proceed: FTPS password is required. Enter it in the sidebar.")
        else:
            with st.spinner("Connecting to Angry Hosting and uploading via FTPS..."):
                # Save uploaded file to temp file for upload
                suffix = pathlib.Path(uploaded_file.name).suffix
                with tempfile.NamedTemporaryFile(delete=False, suffix=suffix) as tmp_file:
                    tmp_file.write(uploaded_file.getbuffer())
                    tmp_path = pathlib.Path(tmp_file.name)

                try:
                    remote_url = upload_via_ftps(
                        tmp_path,
                        plate_selection,
                        album_name.strip() or plate_selection,
                        remote_filename.strip() or uploaded_file.name,
                        creds,
                    )
                    st.success(f"✅ Upload verified! Public URL: [{remote_url}]({remote_url})")

                    if do_sync:
                        with st.spinner("Refreshing live Vercel Blob manifest..."):
                            trigger_sync(creds.get("CRON_SECRET", ""))
                            st.success("✅ Manifest rebuilt and published to Vercel CDN!")

                    plate_slug = plate_selection.lower()
                    live_url = f"https://ericgitonga.com/{plate_slug}"
                    st.balloons()
                    st.info(f"👉 View live now: [{live_url}]({live_url})")
                except Exception as exc:
                    st.error(f"❌ Upload failed: {exc}")
                finally:
                    tmp_path.unlink(missing_ok=True)
