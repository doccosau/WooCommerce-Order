"use server"

import { createClient } from "@/lib/supabase/server"
import { revalidatePath } from "next/cache"
import { z } from "zod"

// Schema validation cho form đồng bộ
const syncFormSchema = z.object({
  website_id: z.string().uuid("ID website không hợp lệ"),
  entity_type: z.enum(["products", "orders", "customers", "all"]),
  sync_all: z.boolean().optional(),
})

export type SyncFormData = z.infer<typeof syncFormSchema>

// Lấy danh sách website
export async function getWebsites() {
  const supabase = createClient()

  const { data, error } = await supabase
    .from("websites")
    .select("*")
    .eq("status", "active")
    .order("name", { ascending: true })

  if (error) {
    console.error("Error fetching websites:", error)
    return []
  }

  return data
}

// Lấy lịch sử đồng bộ
export async function getSyncLogs(limit = 50) {
  const supabase = createClient()

  const { data, error } = await supabase
    .from("sync_logs")
    .select(`
      *,
      website:website_id (
        name
      )
    `)
    .order("started_at", { ascending: false })
    .limit(limit)

  if (error) {
    console.error("Error fetching sync logs:", error)
    return []
  }

  return data
}

// Lấy lịch sử đồng bộ của một website
export async function getWebsiteSyncLogs(websiteId: string, limit = 20) {
  const supabase = createClient()

  const { data, error } = await supabase
    .from("sync_logs")
    .select("*")
    .eq("website_id", websiteId)
    .order("started_at", { ascending: false })
    .limit(limit)

  if (error) {
    console.error("Error fetching website sync logs:", error)
    return []
  }

  return data
}

// Bắt đầu đồng bộ dữ liệu
export async function startSync(formData: FormData) {
  const supabase = createClient()

  // Chuyển đổi FormData thành object
  const rawData = {
    website_id: formData.get("website_id") as string,
    entity_type: formData.get("entity_type") as string,
    sync_all: formData.get("sync_all") === "true",
  }

  // Validate dữ liệu
  const validationResult = syncFormSchema.safeParse(rawData)

  if (!validationResult.success) {
    return {
      success: false,
      errors: validationResult.error.flatten().fieldErrors,
    }
  }

  const { website_id, entity_type, sync_all } = validationResult.data

  try {
    // Nếu đồng bộ tất cả website
    if (sync_all) {
      const { data: websites } = await supabase.from("websites").select("id").eq("status", "active")

      if (!websites || websites.length === 0) {
        return {
          success: false,
          error: "Không tìm thấy website nào để đồng bộ",
        }
      }

      // Tạo các bản ghi sync_logs cho mỗi website
      const syncPromises = websites.map(async (website) => {
        return createSyncLog(website.id, entity_type)
      })

      await Promise.all(syncPromises)
    } else {
      // Đồng bộ một website cụ thể
      await createSyncLog(website_id, entity_type)
    }

    // Revalidate path
    revalidatePath("/sync")
    return { success: true }
  } catch (error) {
    console.error("Error starting sync:", error)
    return {
      success: false,
      error: error instanceof Error ? error.message : "Lỗi không xác định khi bắt đầu đồng bộ",
    }
  }
}

// Tạo bản ghi sync_log
async function createSyncLog(websiteId: string, entityType: string) {
  const supabase = createClient()

  // Tạo bản ghi sync_log
  const { data, error } = await supabase
    .from("sync_logs")
    .insert({
      website_id: websiteId,
      entity_type: entityType,
      status: "running",
      started_at: new Date().toISOString(),
    })
    .select()

  if (error) {
    throw new Error(`Lỗi khi tạo bản ghi sync_log: ${error.message}`)
  }

  // Bắt đầu quá trình đồng bộ (trong thực tế, đây sẽ là một background job)
  // Ở đây chúng ta sẽ mô phỏng quá trình đồng bộ bằng cách cập nhật trạng thái sau một khoảng thời gian
  simulateSyncProcess(data[0].id, websiteId, entityType)

  return data[0]
}

// Mô phỏng quá trình đồng bộ
async function simulateSyncProcess(syncLogId: string, websiteId: string, entityType: string) {
  const supabase = createClient()

  // Trong thực tế, đây sẽ là một quá trình phức tạp hơn để đồng bộ dữ liệu từ WooCommerce
  // Ở đây chúng ta chỉ mô phỏng bằng cách cập nhật trạng thái sau một khoảng thời gian

  // Mô phỏng thời gian đồng bộ
  const syncTime = Math.floor(Math.random() * 5000) + 2000 // 2-7 giây

  setTimeout(async () => {
    // Mô phỏng số lượng mục đã đồng bộ
    const itemsCount = Math.floor(Math.random() * 100) + 10

    // Xác định xem đồng bộ có thành công hay không (90% thành công)
    const isSuccess = Math.random() > 0.1

    // Cập nhật bản ghi sync_log
    const { error } = await supabase
      .from("sync_logs")
      .update({
        status: isSuccess ? "success" : "failed",
        completed_at: new Date().toISOString(),
        items_count: itemsCount,
        error_message: isSuccess ? null : "Lỗi mô phỏng khi đồng bộ dữ liệu",
      })
      .eq("id", syncLogId)

    if (error) {
      console.error("Error updating sync log:", error)
    }

    // Cập nhật thời gian đồng bộ cuối cùng của website
    if (isSuccess) {
      const { error: websiteError } = await supabase
        .from("websites")
        .update({
          last_sync: new Date().toISOString(),
        })
        .eq("id", websiteId)

      if (websiteError) {
        console.error("Error updating website last_sync:", websiteError)
      }
    }

    // Revalidate path để cập nhật UI
    revalidatePath("/sync")
  }, syncTime)
}

// Hủy quá trình đồng bộ
export async function cancelSync(syncLogId: string) {
  const supabase = createClient()

  const { error } = await supabase
    .from("sync_logs")
    .update({
      status: "cancelled",
      completed_at: new Date().toISOString(),
      error_message: "Đồng bộ bị hủy bởi người dùng",
    })
    .eq("id", syncLogId)
    .eq("status", "running")

  if (error) {
    console.error("Error cancelling sync:", error)
    return {
      success: false,
      error: error.message,
    }
  }

  // Revalidate path
  revalidatePath("/sync")
  return { success: true }
}

// Xóa bản ghi sync_log
export async function deleteSyncLog(syncLogId: string) {
  const supabase = createClient()

  const { error } = await supabase.from("sync_logs").delete().eq("id", syncLogId)

  if (error) {
    console.error("Error deleting sync log:", error)
    return {
      success: false,
      error: error.message,
    }
  }

  // Revalidate path
  revalidatePath("/sync")
  return { success: true }
}

// Lấy cài đặt đồng bộ tự động
export async function getAutoSyncSettings() {
  const supabase = createClient()

  const { data, error } = await supabase.from("settings").select("*").eq("key", "auto_sync").single()

  if (error && error.code !== "PGRST116") {
    // PGRST116 là lỗi "không tìm thấy", có thể bỏ qua
    console.error("Error fetching auto sync settings:", error)
    return null
  }

  return data?.value || null
}

// Cập nhật cài đặt đồng bộ tự động
export async function updateAutoSyncSettings(formData: FormData) {
  const supabase = createClient()

  const enabled = formData.get("enabled") === "true"
  const frequency = formData.get("frequency") as string
  const time = formData.get("time") as string
  const entities = Array.from(formData.getAll("entities")) as string[]

  const settingsData = {
    enabled,
    frequency,
    time,
    entities,
    updated_at: new Date().toISOString(),
  }

  // Kiểm tra xem cài đặt đã tồn tại chưa
  const { data: existingSettings } = await supabase.from("settings").select("*").eq("key", "auto_sync").single()

  let result
  if (existingSettings) {
    // Cập nhật cài đặt hiện có
    result = await supabase.from("settings").update({ value: settingsData }).eq("key", "auto_sync")
  } else {
    // Tạo cài đặt mới
    result = await supabase.from("settings").insert({
      key: "auto_sync",
      value: settingsData,
    })
  }

  if (result.error) {
    console.error("Error updating auto sync settings:", result.error)
    return {
      success: false,
      error: result.error.message,
    }
  }

  // Revalidate path
  revalidatePath("/sync")
  return { success: true }
}
